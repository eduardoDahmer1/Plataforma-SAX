<?php

namespace App\Console\Commands;

use App\Models\PaymentTransaction;
use App\Services\RendixPixReconciler;
use App\Services\RendixPixService;
use Illuminate\Console\Command;

class ReconcileRendixPix extends Command
{
    protected $signature = 'rendix:reconcile-pix {--limit=50}';
    protected $description = 'Reconcilia pagamentos Pix Rendix ainda pendentes';

    public function handle(RendixPixReconciler $reconciler): int
    {
        $gateway = RendixPixService::gateway();
        $rendix = RendixPixService::fromPaymentMethod($gateway);
        if (!$rendix->isConfigured() && !$gateway) {
            $this->components->info('Rendix Pix não configurado para o ambiente selecionado.');

            return self::SUCCESS;
        }

        $transactions = PaymentTransaction::query()
            ->where('provider', RendixPixService::PROVIDER)
            ->whereIn('status', ['created', 'pending'])
            ->whereNotNull('external_id')
            ->oldest('updated_at')
            ->limit(max(1, min(200, (int) $this->option('limit'))))
            ->get();

        foreach ($transactions as $transaction) {
            $transactionService = RendixPixService::fromPaymentMethod($gateway, $transaction->environment);
            if ($transactionService->isConfigured()) {
                $reconciler->sync($transaction, $transactionService);
            }
        }

        $this->components->info($transactions->count() . ' transação(ões) consultada(s).');

        return self::SUCCESS;
    }
}
