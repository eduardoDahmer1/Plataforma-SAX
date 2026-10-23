<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\BancardPaymentCompletionService;
use App\Services\BancardV2Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ReconcileBancardPayments extends Command
{
    protected $signature = 'bancard:reconcile-payments {--limit=50}';
    protected $description = 'Reconcilia pedidos Bancard pendentes durante a janela de pagamento';

    public function handle(BancardPaymentCompletionService $completion): int
    {
        if (! Schema::hasTable('checkout_stock_reservations')) {
            $this->components->info('A tabela de reservas ainda não foi implantada.');
            return self::SUCCESS;
        }

        $gateway = PaymentMethod::query()
            ->where('type', 'gateway')
            ->whereRaw('LOWER(name) = ?', ['bancard v2'])
            ->first();
        $bancard = BancardV2Service::fromPaymentMethod($gateway);
        if (! $gateway?->active || ! $bancard->isConfigured()) {
            $this->components->info('Bancard V2 não está ativo ou configurado.');
            return self::SUCCESS;
        }

        $orders = Order::query()
            ->where('payment_method', 'bancard_v2')
            ->where('payment_status', 'pending')
            ->whereNotNull('shop_process_id')
            ->whereHas('stockReservations', fn ($query) => $query
                ->where('status', 'held')
                ->where('expires_at', '>', now()))
            ->oldest('updated_at')
            ->limit(max(1, min(200, (int) $this->option('limit'))))
            ->get();

        foreach ($orders as $order) {
            try {
                $confirmation = $bancard->fetchSingleBuyConfirmation((string) $order->shop_process_id);
                if (! $confirmation) {
                    continue;
                }

                if ($bancard->isApprovedConfirmation($confirmation)) {
                    if ($completion->confirmationMatchesOrder($order, $confirmation)) {
                        $completion->markPaid($order);
                    } else {
                        Log::critical('Bancard confirmation did not match order', ['order_id' => $order->getKey()]);
                    }
                    continue;
                }

                if (trim((string) data_get($confirmation, 'response_code', '')) !== '') {
                    $completion->markFailed($order, $bancard, $confirmation, 'failed');
                }
            } catch (\Throwable $exception) {
                Log::error('Bancard reconciliation failed for order', [
                    'order_id' => $order->getKey(),
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $this->components->info($orders->count().' pedido(s) Bancard consultado(s).');
        return self::SUCCESS;
    }
}
