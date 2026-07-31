<?php

namespace App\Services;

use App\Models\IntegrationMonitor;
use App\Models\IntegrationRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IntegrationMonitorService
{
    public function __construct(
        private AdminNotificationService $notifications,
        private BusinessEventService $events,
    ) {
    }

    public function report(array $payload): IntegrationMonitor
    {
        $occurredAt = Carbon::parse($payload['occurred_at'] ?? now());
        $notifyFailure = false;
        $notifyRecovery = false;
        $event = (string) $payload['event'];

        $monitor = DB::transaction(function () use ($payload, $occurredAt, $event, &$notifyFailure, &$notifyRecovery) {
            IntegrationMonitor::query()->firstOrCreate(
                ['source' => $payload['source']],
                ['name' => $payload['name'] ?? __('messages.integration_products_title')]
            );

            $monitor = IntegrationMonitor::query()
                ->where('source', $payload['source'])
                ->lockForUpdate()
                ->firstOrFail();

            $run = IntegrationRun::query()->firstOrNew([
                'integration_monitor_id' => $monitor->id,
                'run_id' => $payload['run_id'],
            ]);

            if ($run->exists && in_array($run->status, ['success', 'failed'], true) && $event !== 'started') {
                return $monitor;
            }

            $metadata = (array) ($payload['metadata'] ?? []);
            $duration = isset($payload['duration_seconds']) ? max(0, (int) $payload['duration_seconds']) : null;

            if ($event === 'started') {
                $run->fill([
                    'status' => 'running',
                    'started_at' => $run->started_at ?: $occurredAt,
                    'metadata' => $metadata,
                ])->save();

                $monitor->fill([
                    'name' => $payload['name'] ?? $monitor->name,
                    'status' => 'running',
                    'last_run_id' => $payload['run_id'],
                    'last_started_at' => $occurredAt,
                    'last_heartbeat_at' => $occurredAt,
                    'metadata' => $metadata,
                ])->save();

                return $monitor;
            }

            if ($event === 'succeeded') {
                $notifyRecovery = $monitor->outage_started_at !== null
                    || in_array($monitor->status, ['failed', 'stale'], true);

                $run->fill([
                    'status' => 'success',
                    'started_at' => $run->started_at ?: $monitor->last_started_at ?: $occurredAt,
                    'finished_at' => $occurredAt,
                    'duration_seconds' => $duration,
                    'error_code' => null,
                    'error_message' => null,
                    'metadata' => $metadata,
                ])->save();

                $monitor->fill([
                    'name' => $payload['name'] ?? $monitor->name,
                    'status' => 'healthy',
                    'last_run_id' => $payload['run_id'],
                    'last_finished_at' => $occurredAt,
                    'last_success_at' => $occurredAt,
                    'last_heartbeat_at' => $occurredAt,
                    'outage_started_at' => null,
                    'consecutive_failures' => 0,
                    'error_code' => null,
                    'error_message' => null,
                    'duration_seconds' => $duration,
                    'metadata' => $metadata,
                ])->save();

                return $monitor;
            }

            $cooldownMinutes = max(60, (int) config('services.integration_monitor.alert_cooldown_minutes', 720));
            $notifyFailure = $monitor->outage_started_at === null
                || $monitor->last_failure_notification_at === null
                || $monitor->last_failure_notification_at->lte(now()->subMinutes($cooldownMinutes));

            $message = mb_substr((string) ($payload['error_message'] ?? __('messages.integration_finished_with_error')), 0, 2000);
            $code = mb_substr((string) ($payload['error_code'] ?? 'integration_failed'), 0, 80);

            $run->fill([
                'status' => 'failed',
                'started_at' => $run->started_at ?: $monitor->last_started_at ?: $occurredAt,
                'finished_at' => $occurredAt,
                'duration_seconds' => $duration,
                'error_code' => $code,
                'error_message' => $message,
                'metadata' => $metadata,
            ])->save();

            $monitor->fill([
                'name' => $payload['name'] ?? $monitor->name,
                'status' => 'failed',
                'last_run_id' => $payload['run_id'],
                'last_finished_at' => $occurredAt,
                'last_failure_at' => $occurredAt,
                'last_heartbeat_at' => $occurredAt,
                'outage_started_at' => $monitor->outage_started_at ?: $occurredAt,
                'consecutive_failures' => $monitor->consecutive_failures + 1,
                'error_code' => $code,
                'error_message' => $message,
                'duration_seconds' => $duration,
                'metadata' => $metadata,
                'last_failure_notification_at' => $notifyFailure ? now() : $monitor->last_failure_notification_at,
            ])->save();

            return $monitor;
        });

        if ($notifyFailure) {
            $this->sendFailureNotification($monitor, 'integration_failed', __('messages.integration_failure_title'));
        } elseif ($notifyRecovery) {
            $this->sendRecoveryNotification($monitor);
        }

        return $monitor->fresh();
    }

    public function checkForStaleIntegrations(): int
    {
        $staleMinutes = max(30, (int) config('services.integration_monitor.stale_after_minutes', 180));
        $threshold = now()->subMinutes($staleMinutes);
        $marked = 0;

        IntegrationMonitor::query()->eachById(function (IntegrationMonitor $monitor) use ($threshold, $staleMinutes, &$marked) {
            $heartbeat = $monitor->last_heartbeat_at ?: $monitor->created_at;
            if (!$heartbeat || $heartbeat->gt($threshold)) {
                return;
            }

            if ($monitor->status === 'stale') {
                return;
            }

            $monitor->update([
                'status' => 'stale',
                'outage_started_at' => $monitor->outage_started_at ?: $heartbeat,
                'error_code' => 'heartbeat_stale',
                'error_message' => __('messages.integration_stale_message', [
                    'date' => $heartbeat->format('d/m/Y H:i:s'),
                    'minutes' => $staleMinutes,
                ]),
                'last_failure_notification_at' => now(),
            ]);

            $marked++;
            $this->sendFailureNotification($monitor->fresh(), 'integration_stale', __('messages.integration_stale_title'));
        });

        return $marked;
    }

    private function sendFailureNotification(IntegrationMonitor $monitor, string $type, string $title): void
    {
        $message = $monitor->error_message ?: __('messages.integration_unavailable');
        $this->notifications->notifyAdmins($type, $title, mb_substr($message, 0, 500), '/admin#integration-monitor', [
            'source' => $monitor->source,
            'status' => $monitor->status,
            'error_code' => $monitor->error_code,
        ]);
        $this->events->record('integration', $title, $message, 'error', null, null, $monitor->source);
    }

    private function sendRecoveryNotification(IntegrationMonitor $monitor): void
    {
        $message = __('messages.integration_recovered_message', [
            'date' => optional($monitor->last_success_at)->format('d/m/Y H:i'),
        ]);
        $title = __('messages.integration_recovered_title');
        $this->notifications->notifyAdmins('integration_recovered', $title, $message, '/admin#integration-monitor', [
            'source' => $monitor->source,
            'status' => $monitor->status,
        ]);
        $this->events->record('integration', $title, $message, 'success', null, null, $monitor->source);
    }
}
