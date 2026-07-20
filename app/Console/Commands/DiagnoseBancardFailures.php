<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\BancardV2Service;
use App\Services\BusinessEventService;
use Illuminate\Console\Command;

class DiagnoseBancardFailures extends Command
{
    protected $signature = 'bancard:diagnose-failures {--days=30}';
    protected $description = 'Consulta o Bancard e completa os motivos das transações não aprovadas';

    public function handle(BusinessEventService $events): int
    {
        $gateway = PaymentMethod::where('type', 'gateway')->whereRaw('LOWER(name) = ?', ['bancard v2'])->first();
        $bancard = BancardV2Service::fromPaymentMethod($gateway);
        if (!$bancard->isConfigured()) {
            $this->error('Credenciais Bancard V2 não configuradas.');
            return self::FAILURE;
        }

        $updated = 0;
        Order::where('payment_method', 'bancard_v2')->whereNotNull('shop_process_id')
            ->where('created_at', '>=', now()->subDays(max(1, (int) $this->option('days'))))
            ->where(fn ($query) => $query->where('status', 'failed')->orWhere('payment_status', 'failed'))
            ->get()->each(function (Order $order) use ($bancard, $events, &$updated) {
                $confirmation = $bancard->fetchSingleBuyConfirmation((string) $order->shop_process_id);
                if (!$confirmation || $bancard->isApprovedConfirmation($confirmation)) return;
                $code = trim((string) data_get($confirmation, 'response_code')) ?: null;
                $message = $bancard->describeFailure($confirmation, 'failed');
                $order->update(['status' => 'failed', 'payment_status' => 'failed', 'payment_response_code' => $code, 'payment_response_message' => $message, 'payment_failed_at' => $order->payment_failed_at ?: now()]);
                $events->record('payment', 'Pagamento Bancard não aprovado', $message, 'warning', $order->user_id, $order->id, $code ? 'Bancard '.$code : $order->shop_process_id);
                $updated++;
            });
        $this->info("{$updated} pedido(s) diagnosticado(s).");
        return self::SUCCESS;
    }
}
