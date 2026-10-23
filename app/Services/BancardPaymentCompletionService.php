<?php

namespace App\Services;

use App\Mail\OrderPaidMail;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BancardPaymentCompletionService
{
    public function __construct(
        private ReceiptService $receipts,
        private BusinessEventService $events,
        private CheckoutStockReservationService $stockReservations,
        private CuponService $cupons,
    ) {
    }

    public function markPaid(Order $order): Order
    {
        $newlyPaid = false;

        $order = DB::transaction(function () use ($order, &$newlyPaid): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            if (! $locked->isPaid()) {
                $this->stockReservations->commitOrder($locked);
                $this->cupons->garantirUsoDoPedidoPago($locked);
                $locked->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'payment_response_code' => '00',
                    'payment_response_message' => 'Pagamento aprovado pelo Bancard.',
                    'payment_failed_at' => null,
                ]);
                $newlyPaid = true;
            }

            Cart::where('user_id', $locked->user_id)->delete();

            return $locked->fresh();
        }, 3);

        if ($newlyPaid) {
            try {
                $this->receipts->issueForOrder($order);
            } catch (\Throwable $exception) {
                Log::error('Error al emitir recibo para pedido Bancard V2', [
                    'order_id' => $order->getKey(),
                    'message' => $exception->getMessage(),
                ]);
            }

            try {
                $order->loadMissing('receipt');
                Mail::to($order->email)->send(new OrderPaidMail($order));
            } catch (\Throwable $exception) {
                Log::error('Error al enviar correo de confirmación Bancard V2', [
                    'order_id' => $order->getKey(),
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $order;
    }

    public function markFailed(Order $order, BancardV2Service $bancard, ?array $confirmation, string $status = ''): Order
    {
        $code = trim((string) data_get($confirmation, 'response_code', '')) ?: null;
        $message = $bancard->describeFailure($confirmation, $status);
        $alreadyRecorded = false;
        $failed = false;

        $order = DB::transaction(function () use ($order, $code, $message, &$alreadyRecorded, &$failed): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            if ($locked->isPaid()) {
                return $locked;
            }

            $alreadyRecorded = $locked->payment_status === 'failed'
                && (string) $locked->payment_response_code === (string) $code
                && (string) $locked->payment_response_message === $message;

            $this->stockReservations->releaseOrder($locked, $code ? 'bancard_'.$code : 'bancard_failed');
            $locked->refresh();
            $locked->update([
                'status' => 'failed',
                'payment_status' => 'failed',
                'payment_response_code' => $code,
                'payment_response_message' => $message,
                'payment_failed_at' => $locked->payment_failed_at ?: now(),
            ]);
            $failed = true;

            return $locked->fresh();
        }, 3);

        if ($failed && ! $alreadyRecorded) {
            $this->events->record(
                'payment',
                'Pagamento Bancard não aprovado',
                $message,
                'warning',
                $order->user_id,
                $order->getKey(),
                $code ? 'Bancard '.$code : (string) $order->shop_process_id,
            );
        }

        return $order;
    }

    public function confirmationMatchesOrder(Order $order, array $confirmation): bool
    {
        $currency = strtoupper(trim((string) data_get($confirmation, 'currency', 'PYG')));
        $amount = data_get($confirmation, 'amount');

        if ($currency !== 'PYG') {
            return false;
        }

        return $amount === null
            || abs((float) $amount - (float) $order->payment_amount) <= 0.01;
    }
}
