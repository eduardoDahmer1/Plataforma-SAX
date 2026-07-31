<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\BusinessEventService;
use App\Services\RendixPixReconciler;
use App\Services\RendixPixService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RendixPixController extends Controller
{
    private RendixPixService $rendix;
    private $gateway;

    public function __construct(
        private RendixPixReconciler $reconciler,
        private BusinessEventService $events,
    ) {
        $this->gateway = RendixPixService::gateway();
        $this->rendix = RendixPixService::fromPaymentMethod($this->gateway);
    }

    public function checkoutPage(Order $order): View|RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->payment_method !== RendixPixService::PROVIDER) {
            abort(404);
        }

        if ($order->isPaid()) {
            return redirect()->route('user.orders.show', $order)
                ->with('success', 'O pagamento Pix deste pedido já foi confirmado.');
        }

        if (!$this->gateway?->active || !$this->rendix->isConfigured()) {
            return redirect()->route('user.orders.show', $order)
                ->with('warning', 'O Pix está temporariamente indisponível. Confira a configuração do gateway.');
        }

        $transaction = $order->paymentTransactions()
            ->where('provider', RendixPixService::PROVIDER)
            ->latest()
            ->first();

        if ($transaction?->external_id) {
            $transactionService = RendixPixService::fromPaymentMethod($this->gateway, $transaction->environment);
            $transaction = $this->reconciler->sync($transaction, $transactionService);
            $order->refresh();
        }

        if ($order->isPaid()) {
            return redirect()->route('user.orders.show', $order)
                ->with('success', 'Pagamento Pix confirmado!');
        }

        if (
            !$transaction
            || $transaction->environment !== $this->rendix->environment()
            || !$transaction->isPayable()
        ) {
            $transaction = $this->createTransaction($order);
            if (!$transaction || !$transaction->isPayable()) {
                return redirect()->route('user.orders.show', $order)
                    ->with('warning', 'Não foi possível gerar o Pix agora. Tente novamente em alguns instantes.');
            }
        }

        return view('payment.rendix-pix', [
            'order' => $order,
            'transaction' => $transaction,
            'sandbox' => $this->rendix->environment() === 'sandbox',
        ]);
    }

    public function status(Order $order): JsonResponse
    {
        $this->authorizeOrder($order);

        $transaction = $order->paymentTransactions()
            ->where('provider', RendixPixService::PROVIDER)
            ->latest()
            ->firstOrFail();

        $transactionService = RendixPixService::fromPaymentMethod($this->gateway, $transaction->environment);
        $transaction = $this->reconciler->sync($transaction, $transactionService);
        $order->refresh();

        return response()->json([
            'status' => $order->isPaid() ? 'paid' : $transaction->status,
            'provider_status' => $transaction->provider_status,
            'message' => $order->isPaid()
                ? 'Pagamento confirmado!'
                : ($transaction->failure_message ?: 'Aguardando pagamento Pix.'),
            'expires_at' => $transaction->expires_at?->toIso8601String(),
            'redirect_url' => $order->isPaid() ? route('user.orders.show', $order) : null,
        ]);
    }

    public function renew(Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->isPaid()) {
            return redirect()->route('user.orders.show', $order)
                ->with('success', 'Pagamento já confirmado.');
        }

        $latest = $order->paymentTransactions()
            ->where('provider', RendixPixService::PROVIDER)
            ->latest()
            ->first();

        if ($latest?->external_id) {
            $transactionService = RendixPixService::fromPaymentMethod($this->gateway, $latest->environment);
            $latest = $this->reconciler->sync($latest, $transactionService);
            $order->refresh();
        }

        if ($order->isPaid()) {
            return redirect()->route('user.orders.show', $order)
                ->with('success', 'Pagamento Pix confirmado!');
        }

        if ($latest?->environment === $this->rendix->environment() && $latest->isPayable()) {
            return redirect()->route('checkout.rendix.pix', $order);
        }

        $this->createTransaction($order);

        return redirect()->route('checkout.rendix.pix', $order);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $saleId = $this->rendix->extractWebhookSaleId($payload);
        $controlNumber = $this->rendix->extractWebhookControlNumber($payload);

        Log::info('Rendix Pix webhook received', [
            'sale_id' => $saleId,
            'control_number' => $controlNumber,
            'status' => data_get($payload, 'status') ?? data_get($payload, 'data.status'),
        ]);

        if (!$saleId && !$controlNumber) {
            return response()->json(['success' => false, 'message' => 'Identificador da venda ausente'], 422);
        }

        $transaction = PaymentTransaction::query()
            ->where('provider', RendixPixService::PROVIDER)
            ->where(function ($query) use ($saleId, $controlNumber) {
                if ($saleId) {
                    $query->where('external_id', $saleId);
                }
                if ($controlNumber) {
                    $saleId ? $query->orWhere('control_number', $controlNumber) : $query->where('control_number', $controlNumber);
                }
            })
            ->first();

        if (!$transaction) {
            Log::warning('Rendix Pix webhook sale not found', ['sale_id' => $saleId]);

            return response()->json(['success' => false, 'message' => 'Venda não encontrada'], 404);
        }

        // O webhook é apenas um gatilho. A confirmação é sempre consultada na API autenticada.
        $transactionService = RendixPixService::fromPaymentMethod($this->gateway, $transaction->environment);
        $this->reconciler->sync($transaction, $transactionService);

        return response()->json(['success' => true]);
    }

    public function terms(): Response
    {
        if (!$this->gateway?->active || !$this->rendix->isConfigured()) {
            abort(404);
        }

        try {
            $response = $this->rendix->getTermsDocument();
        } catch (\Throwable $e) {
            Log::warning('Rendix Pix terms document failed', ['message' => $e->getMessage()]);
            abort(502, 'Não foi possível carregar os Termos e Condições da Rendix.');
        }
        $contents = base64_decode((string) data_get($response, 'data.fileContents', ''), true);

        if (!$response['ok'] || $contents === false || !str_starts_with($contents, '%PDF')) {
            abort(502, 'Não foi possível carregar os Termos e Condições da Rendix.');
        }

        $filename = basename((string) data_get($response, 'data.fileDownloadName', 'termos-rendix.pdf'));

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function createTransaction(Order $order): ?PaymentTransaction
    {
        $transaction = $order->paymentTransactions()->create([
            'provider' => RendixPixService::PROVIDER,
            'environment' => $this->rendix->environment(),
            'control_number' => 'SAX-PIX-' . $order->id . '-' . strtoupper(Str::random(8)),
            'status' => 'pending',
            'foreign_currency' => 'USD',
            'foreign_amount' => number_format((float) $order->total, 2, '.', ''),
        ]);

        try {
            $response = $this->rendix->createSale(
                $order,
                $transaction,
                route('rendix.pix.webhook'),
            );
        } catch (\Throwable $e) {
            $this->markCreationFailed($transaction, $e->getMessage());
            return $transaction->fresh();
        }

        if (!$response['ok']) {
            $message = trim((string) data_get($response, 'data.message', ''));
            $this->markCreationFailed(
                $transaction,
                $message !== '' ? $message : 'A Rendix não conseguiu criar a venda Pix.',
                (string) $response['status'],
            );

            return $transaction->fresh();
        }

        $sale = $this->rendix->extractSaleData($response['data']);
        $saleId = trim((string) data_get($sale, 'saleId', ''));
        $pix = trim((string) data_get($sale, 'pixCopyPaste', ''));
        $qr = trim((string) data_get($sale, 'qrCodeBase64', ''));

        if ($saleId === '' || $pix === '' || $qr === '') {
            $this->markCreationFailed($transaction, 'A Rendix retornou uma venda Pix incompleta.');

            return $transaction->fresh();
        }

        $expiration = data_get($sale, 'qrCodeExpiration');
        try {
            $expiresAt = $expiration ? Carbon::parse((string) $expiration) : now()->addMinutes(5);
        } catch (\Throwable) {
            $expiresAt = now()->addMinutes(5);
        }

        $transaction->update([
            'external_id' => $saleId,
            'status' => 'pending',
            'provider_status' => '2',
            'national_currency' => 'BRL',
            'national_amount' => data_get($sale, 'priceNationalCurrency'),
            'exchange_rate' => data_get($sale, 'vetTax'),
            'pix_copy_paste' => $pix,
            'qr_code_base64' => preg_replace('#^data:image/[^;]+;base64,#i', '', $qr),
            'expires_at' => $expiresAt,
            'provider_payload' => $sale,
        ]);

        $order->update([
            'payment_status' => 'pending',
            'payment_response_code' => '2',
            'payment_response_message' => 'Pix gerado e aguardando pagamento.',
            'payment_currency' => 'BRL',
            'payment_amount' => data_get($sale, 'priceNationalCurrency'),
            'payment_exchange_rate' => data_get($sale, 'vetTax'),
        ]);

        return $transaction->fresh();
    }

    private function markCreationFailed(
        PaymentTransaction $transaction,
        string $message,
        ?string $code = null,
    ): void {
        $transaction->update([
            'status' => 'failed',
            'failure_code' => $code,
            'failure_message' => $message,
        ]);

        $transaction->order()->update([
            'payment_response_code' => $code,
            'payment_response_message' => $message,
            'payment_failed_at' => now(),
        ]);

        $order = $transaction->order;
        $this->events->record(
            'payment',
            'Falha ao gerar Pix',
            $message,
            'error',
            $order?->user_id,
            $transaction->order_id,
            $transaction->control_number,
        );

        Log::error('Rendix Pix sale creation failed', [
            'order_id' => $transaction->order_id,
            'transaction_id' => $transaction->id,
            'message' => $message,
        ]);
    }

    private function authorizeOrder(Order $order): void
    {
        if (!auth()->check() || (int) auth()->id() !== (int) $order->user_id) {
            abort(403, 'Acesso negado ao pedido.');
        }
    }
}
