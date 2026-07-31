<?php

namespace App\Console\Commands;

use App\Models\IntegrationMonitor;
use App\Services\IntegrationMonitorService;
use Illuminate\Console\Command;

class CheckIntegrationHealth extends Command
{
    protected $signature = 'integration:check-health';
    protected $description = 'Detecta integrações que deixaram de enviar heartbeat';

    public function handle(IntegrationMonitorService $service): int
    {
        IntegrationMonitor::query()->firstOrCreate(
            ['source' => 'catalog'],
            ['name' => 'Integração de produtos', 'status' => 'never_reported']
        );

        $marked = $service->checkForStaleIntegrations();
        $this->components->info($marked.' integração(ões) marcada(s) como sem comunicação.');

        return self::SUCCESS;
    }
}
