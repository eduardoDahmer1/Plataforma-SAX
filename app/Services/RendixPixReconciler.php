<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class RendixPixReconciler
{
    public function __construct(
        private RendixPaymentCompletionService $completion,
        private BusinessEventService $events,
    ) {
    }

    public function sync(PaymentTransaction $transaction, RendixPixService $rendix): PaymentTransaction
    {
        if (!$transaction->external_id) {
            return $transaction;
        }

        try {
            $response = $rendix->getSale((string) $transaction->external_id);
        } catch (\Throwable $e) {
            Log::warning('Rendix Pix sale query failed', [
                'transaction_id' => $transaction->id,
                'sale_id' => $transaction->external_id,
                'message' => $e->getMessage(),
            ]);

            return $transaction->fresh();
        }

        if (!$response['ok']) {
            Log::warning('Rendix Pix sale query returned error', [
                'transaction_id' => $transaction->id,
                'sale_id' => $transaction->external_id,
                'http_status' => $response['status'],
                'message' => data_get($response, 'data.message'),
            ]);

            return $transaction->fresh();
        }

        $sale = $rendix->extractSaleData($response['data']);
        $providerStatus = $rendix->providerStatus($sale);
        $localStatus = $rendix->localStatus($providerStatus);

        if ($localStatus === 'paid' && !$this->matchesExpectedSale($transaction, $sale)) {
            $message = 'A confirmação Rendix não corresponde ao número, moeda ou valor esperado para o pedido.';
            $transaction->update([
                'status' => 'verification_failed',
                'provider_status' => $providerStatus,
                'failure_code' => 'sale_mismatch',
                'failure_message' => $message,
                'provider_payload' => $sale,
            ]);
            $this->events->record(
                'payment',
                'Confirmação Pix divergente',
                $message,
                'error',
                $transaction->order?->user_id,
                $transaction->order_id,
                (string) $transaction->external_id,
            );

            return $transaction->fresh();
        }

        if ($localStatus === 'paid') {
            $this->completion->markPaid($transaction, $sale);

            return $transaction->fresh();
        }

        $description = $rendix->statusDescription($providerStatus);
        $transaction->update([
            'status' => $localStatus,
            'provider_status' => $providerStatus,
            'national_amount' => data_get($sale, 'priceNationalCurrency', $transaction->national_amount),
            'foreign_amount' => data_get($sale, 'priceInForeignCurrency', $transaction->foreign_amount),
            'exchange_rate' => data_get($sale, 'vetTax', $transaction->exchange_rate),
            'refunded_at' => $localStatus === 'refunded' ? ($transaction->refunded_at ?: now()) : $transaction->refunded_at,
            'failure_code' => in_array($localStatus, ['failed', 'expired'], true) ? $providerStatus : null,
            'failure_message' => in_array($localStatus, ['failed', 'expired'], true) ? $description : null,
            'provider_payload' => $sale,
        ]);

        if ($localStatus === 'refunded') {
            $transaction->order()->update([
                'status' => 'refunded',
                'payment_status' => 'refunded',
                'payment_response_code' => '12',
                'payment_response_message' => $description,
            ]);
        }

        return $transaction->fresh();
    }

    private function matchesExpectedSale(PaymentTransaction $transaction, array $sale): bool
    {
        $controlNumber = trim((string) data_get($sale, 'controlNumber', ''));
        if ($controlNumber !== '' && !hash_equals($transaction->control_number, $controlNumber)) {
            return false;
        }

        $currency = strtoupper(trim((string) data_get($sale, 'currency', '')));
        if ($currency !== '' && $currency !== strtoupper((string) $transaction->foreign_currency)) {
            return false;
        }

        $amount = data_get($sale, 'priceInForeignCurrency');
        if ($amount !== null && abs((float) $amount - (float) $transaction->foreign_amount) > 0.01) {
            return false;
        }

        return true;
    }
}
