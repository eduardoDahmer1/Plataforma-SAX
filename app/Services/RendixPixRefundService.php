<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RendixPixRefundService
{
    public function __construct(private BusinessEventService $events)
    {
    }

    public function refund(Order $order, User $admin): PaymentTransaction
    {
        return Cache::lock("rendix-pix-refund:{$order->getKey()}", 45)->block(5, function () use ($order, $admin) {
            $order = Order::query()->with('items.product')->findOrFail($order->getKey());

            if ($order->payment_method !== RendixPixService::PROVIDER || $order->payment_status !== 'paid') {
                throw new RuntimeException(__('messages.rendix_refund_not_available'));
            }

            $transaction = $order->paymentTransactions()
                ->where('provider', RendixPixService::PROVIDER)
                ->where('status', 'paid')
                ->whereNotNull('external_id')
                ->latest('id')
                ->first();

            if (! $transaction || $transaction->refunded_at) {
                throw new RuntimeException(__('messages.rendix_refund_not_available'));
            }

            $amount = round((float) ($transaction->foreign_amount ?: $order->total), 2);
            if ($amount <= 0) {
                throw new RuntimeException(__('messages.rendix_refund_invalid_amount'));
            }

            $gateway = RendixPixService::gateway();
            if (! $gateway) {
                throw new RuntimeException(__('messages.rendix_credentials_not_configured'));
            }

            // Desativar o Pix para novas compras não pode impedir a devolução
            // de uma cobrança que já foi confirmada anteriormente.
            $rendix = RendixPixService::fromPaymentMethod($gateway, $transaction->environment);
            if (! $rendix->isConfigured()) {
                throw new RuntimeException(__('messages.rendix_credentials_not_configured'));
            }

            $preview = $rendix->previewRefund((string) $transaction->external_id, $amount);
            if (! ($preview['ok'] ?? false)) {
                $this->logFailure('preview', $order, $transaction, $preview, $admin);
                throw new RuntimeException($this->providerMessage($preview, __('messages.rendix_refund_preview_failed')));
            }

            $refund = $rendix->refundSale((string) $transaction->external_id, $amount);
            if (! ($refund['ok'] ?? false)) {
                // Se a Rendix confirmou financeiramente, mas a resposta se perdeu,
                // a consulta evita repetir a operação e recupera o estado real.
                $currentSale = $rendix->getSale((string) $transaction->external_id);
                $currentData = ($currentSale['ok'] ?? false)
                    ? $rendix->extractSaleData($currentSale['data'] ?? [])
                    : [];

                if ($rendix->providerStatus($currentData) === '12') {
                    $refund = $currentSale;
                } else {
                    $this->logFailure('confirm', $order, $transaction, $refund, $admin);
                    throw new RuntimeException($this->providerMessage($refund, __('messages.rendix_refund_failed')));
                }
            }

            $refundData = $rendix->extractSaleData($refund['data'] ?? []);
            $providerStatus = $rendix->providerStatus($refundData);
            if ($providerStatus !== '' && $providerStatus !== '12') {
                $this->logFailure('unexpected_status', $order, $transaction, $refund, $admin);
                throw new RuntimeException(__('messages.rendix_refund_failed'));
            }

            DB::transaction(function () use ($order, $transaction, $preview, $refund, $refundData, $amount, $admin): void {
                $transaction->update([
                    'status' => 'refunded',
                    'provider_status' => (string) ($refundData['status'] ?? '12'),
                    'refunded_at' => now(),
                    'failure_code' => null,
                    'failure_message' => null,
                    'provider_payload' => array_merge($transaction->provider_payload ?: [], [
                        'refund_preview' => $preview['data'] ?? [],
                        'refund' => $refund['data'] ?? [],
                        'refund_requested_amount' => $amount,
                        'refund_confirmed_at' => now()->toIso8601String(),
                    ]),
                ]);

                if (! in_array($order->status, ['canceled', 'cancelled', 'refunded'], true)) {
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product->increment('stock', $item->quantity);
                        }
                    }
                }

                $order->update([
                    'status' => 'canceled',
                    'payment_status' => 'refunded',
                    'payment_response_code' => (string) ($refundData['status'] ?? '12'),
                    'payment_response_message' => __('messages.rendix_refund_completed_message'),
                    'refund_request_status' => 'completed',
                    'refund_request_resolved_at' => now(),
                    'refund_request_resolved_by' => $admin->getKey(),
                ]);
            });

            $reference = $order->order_number ?: $order->getKey();
            $this->events->record(
                'payment',
                __('messages.rendix_refund_success'),
                __('messages.rendix_refund_event_message', ['reference' => $reference, 'amount' => number_format($amount, 2, ',', '.')]),
                'warning',
                $order->user_id,
                $order->getKey(),
                (string) $transaction->external_id,
            );

            Log::notice('Rendix Pix refund completed', [
                'order_id' => $order->getKey(),
                'transaction_id' => $transaction->getKey(),
                'sale_id' => $transaction->external_id,
                'amount' => $amount,
                'environment' => $transaction->environment,
                'admin_id' => $admin->getKey(),
            ]);

            return $transaction->fresh();
        });
    }

    private function providerMessage(array $response, string $fallback): string
    {
        $status = (int) ($response['status'] ?? 0);
        $message = trim((string) data_get($response, 'data.message'));
        $normalizedMessage = mb_strtolower($message);

        if (
            in_array($status, [401, 403], true)
            || str_contains($normalizedMessage, 'permission denied')
            || str_contains($normalizedMessage, 'sem permiss')
            || str_contains($normalizedMessage, 'sin permiso')
        ) {
            return __('messages.rendix_refund_permission_denied');
        }

        return $message ?: $fallback;
    }

    private function logFailure(
        string $step,
        Order $order,
        PaymentTransaction $transaction,
        array $response,
        User $admin,
    ): void {
        Log::warning('Rendix Pix refund failed', [
            'step' => $step,
            'order_id' => $order->getKey(),
            'transaction_id' => $transaction->getKey(),
            'sale_id' => $transaction->external_id,
            'http_status' => $response['status'] ?? null,
            'provider_message' => data_get($response, 'data.message'),
            'admin_id' => $admin->getKey(),
        ]);
    }
}
