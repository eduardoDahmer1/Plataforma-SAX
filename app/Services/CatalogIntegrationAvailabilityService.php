<?php

namespace App\Services;

use App\Models\IntegrationMonitor;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CatalogIntegrationAvailabilityService
{
    private ?array $memoizedStatus = null;

    /**
     * Fonte única para a disponibilidade comercial do catálogo.
     * A expiração é calculada em cada requisição, para que o bloqueio de
     * compras não dependa exclusivamente da próxima execução do scheduler.
     */
    public function status(): array
    {
        if ($this->memoizedStatus !== null) {
            return $this->memoizedStatus;
        }

        try {
            if (! Schema::hasTable('integration_monitors')) {
                return $this->memoizedStatus = $this->unavailable('monitor_not_initialized');
            }

            $monitor = IntegrationMonitor::query()->where('source', 'catalog')->first();

            if (! $monitor || ! $monitor->last_heartbeat_at) {
                return $this->memoizedStatus = $this->unavailable('never_reported');
            }

            if (in_array($monitor->status, ['failed', 'stale', 'never_reported'], true)) {
                return $this->memoizedStatus = $this->unavailable(
                    $monitor->error_code ?: $monitor->status,
                    $monitor
                );
            }

            $staleAfterMinutes = max(
                30,
                (int) config('services.integration_monitor.failure_alert_after_minutes', 1440)
            );

            if ($monitor->last_heartbeat_at->lt(now()->subMinutes($staleAfterMinutes))) {
                return $this->memoizedStatus = $this->unavailable('heartbeat_stale', $monitor);
            }

            return $this->memoizedStatus = [
                'available' => true,
                'code' => null,
                'monitor_status' => $monitor->status,
                'last_heartbeat_at' => $monitor->last_heartbeat_at,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return $this->memoizedStatus = $this->unavailable('monitor_unavailable');
        }
    }

    public function isAvailable(): bool
    {
        return (bool) $this->status()['available'];
    }

    private function unavailable(string $code, ?IntegrationMonitor $monitor = null): array
    {
        return [
            'available' => false,
            'code' => $code,
            'monitor_status' => $monitor?->status,
            'last_heartbeat_at' => $monitor?->last_heartbeat_at,
        ];
    }
}
