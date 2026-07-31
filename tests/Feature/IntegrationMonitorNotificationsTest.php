<?php

namespace Tests\Feature;

use App\Mail\IntegrationAlertMail;
use App\Models\User;
use App\Services\IntegrationMonitorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class IntegrationMonitorNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_integration_only_sends_an_email_after_24_hours(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['user_type' => 1]);
        $failureAt = now();

        $service = app(IntegrationMonitorService::class);
        $service->report($this->payload('failed', 'run-1', [
            'occurred_at' => $failureAt->toIso8601String(),
            'error_code' => 'catalog_timeout',
            'error_message' => 'El catálogo no respondió a tiempo.',
        ]));
        $service->report($this->payload('failed', 'run-2', [
            'occurred_at' => $failureAt->copy()->addHours(2)->toIso8601String(),
            'error_code' => 'catalog_timeout',
            'error_message' => 'El catálogo continúa sin responder.',
        ]));

        Mail::assertNothingSent();

        Carbon::setTestNow($failureAt->copy()->addDay()->addMinute());
        $service->checkForStaleIntegrations();
        Carbon::setTestNow();

        Mail::assertSent(IntegrationAlertMail::class, function (IntegrationAlertMail $mail) use ($admin) {
            return $mail->hasTo($admin->email)
                && $mail->type === 'integration_failed'
                && $mail->details['error_code'] === 'catalog_timeout';
        });

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => 'integration_failed',
        ]);
    }

    public function test_recovery_does_not_send_an_email(): void
    {
        Mail::fake();
        User::factory()->create(['user_type' => 1]);

        $service = app(IntegrationMonitorService::class);
        $service->report($this->payload('failed', 'run-1', [
            'error_code' => 'catalog_timeout',
            'error_message' => 'Falha temporária.',
        ]));
        $service->report($this->payload('succeeded', 'run-2', [
            'duration_seconds' => 18,
        ]));

        Mail::assertNothingSent();
    }

    public function test_normal_success_does_not_send_an_email(): void
    {
        Mail::fake();
        User::factory()->create(['user_type' => 1]);

        $service = app(IntegrationMonitorService::class);
        $service->report($this->payload('started', 'run-1'));
        $service->report($this->payload('succeeded', 'run-1', [
            'duration_seconds' => 12,
        ]));

        Mail::assertNothingSent();
    }

    private function payload(string $event, string $runId, array $extra = []): array
    {
        return array_merge([
            'source' => 'catalog',
            'name' => 'Integração de produtos',
            'event' => $event,
            'run_id' => $runId,
            'occurred_at' => now()->toIso8601String(),
            'metadata' => [],
        ], $extra);
    }
}
