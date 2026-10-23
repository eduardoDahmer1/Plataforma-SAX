<?php

namespace App\Services;

use App\Mail\OrderPaidMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RendixPaymentCompletionService
{
    public function __construct(
        private ReceiptService $receipts,
        private BusinessEventService $events,
        private CheckoutStockReservationService $stockReservations,
        private CuponService $cupons,
    ) {
    }

    public function markPaid(PaymentTransaction $transaction, array $saleData): Order
    {
        $newlyPaid = false;

        $order = DB::transaction(function () use ($transaction, $saleData, &$newlyPaid) {
            $lockedTransaction = PaymentTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            $order = Order::query()->lockForUpdate()->findOrFail($lockedTransaction->order_id);

            $lockedTransaction->update([
                'status' => 'paid',
                'provider_status' => '3',
                'national_amount' => data_get($saleData, 'priceNationalCurrency', $lockedTransaction->national_amount),
                'foreign_amount' => data_get($saleData, 'priceInForeignCurrency', $lockedTransaction->foreign_amount),
                'exchange_rate' => data_get($saleData, 'vetTax', $lockedTransaction->exchange_rate),
                'paid_at' => $lockedTransaction->paid_at ?: now(),
                'failure_code' => null,
                'failure_message' => null,
                'provider_payload' => $saleData,
            ]);

            if (!$order->isPaid()) {
                $newlyPaid = true;
                $this->stockReservations->commitOrder($order);
                $this->cupons->garantirUsoDoPedidoPago($order);
                $order->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'payment_response_code' => '3',
                    'payment_response_message' => __('messages.pix_payment_confirmed_by_rendix'),
                    'payment_failed_at' => null,
                    'payment_currency' => 'BRL',
                    'payment_amount' => data_get($saleData, 'priceNationalCurrency', $lockedTransaction->national_amount),
                    'payment_exchange_rate' => data_get($saleData, 'vetTax', $lockedTransaction->exchange_rate),
                ]);

            }

            Cart::where('user_id', $order->user_id)->delete();

            return $order->fresh();
        });

        if ($newlyPaid) {
            $this->events->record(
                'payment',
                __('messages.pix_payment_confirmed_title'),
                __('messages.pix_payment_confirmed_event'),
                'success',
                $order->user_id,
                $order->id,
                (string) $transaction->external_id,
            );

            try {
                $this->receipts->issueForOrder($order);
            } catch (\Throwable $e) {
                Log::error('Erro ao emitir recibo do pagamento Rendix Pix', [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]);
            }

            try {
                $order->loadMissing('receipt');
                Mail::to($order->email)->send(new OrderPaidMail($order));
            } catch (\Throwable $e) {
                Log::error('Erro ao enviar confirmação do pagamento Rendix Pix', [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $order;
    }
}
