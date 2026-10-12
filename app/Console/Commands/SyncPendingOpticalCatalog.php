<?php

namespace App\Console\Commands;

use App\Services\OpticalProductSyncQueueService;
use Illuminate\Console\Command;

class SyncPendingOpticalCatalog extends Command
{
    protected $signature = 'catalog:sync-pending
        {--max-families=1 : Número máximo de famílias por execução}
        {--max-seconds=45 : Orçamento de tempo da execução}';

    protected $description = 'Sincroniza famílias ópticas pendentes fora das requisições web';

    public function handle(OpticalProductSyncQueueService $queue): int
    {
        $result = $queue->processPending(
            (int) $this->option('max-families'),
            (int) $this->option('max-seconds'),
        );

        $this->components->info(sprintf(
            'Famílias: %d processada(s), %d falha(s), %d pendência(s).',
            $result['processed'],
            $result['failed'],
            $result['pending'],
        ));

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
