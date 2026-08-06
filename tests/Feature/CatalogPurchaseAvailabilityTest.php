<?php

namespace Tests\Feature;

use App\Models\IntegrationMonitor;
use App\Services\CatalogIntegrationAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPurchaseAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_healthy_heartbeat_keeps_purchases_available(): void
    {
        $this->monitor(['status' => 'healthy', 'last_heartbeat_at' => now()]);

        $this->assertTrue(app(CatalogIntegrationAvailabilityService::class)->isAvailable());
    }

    public function test_failed_monitor_blocks_purchases(): void
    {
        $this->monitor([
            'status' => 'failed',
            'last_heartbeat_at' => now(),
            'error_code' => 'upstream_http_500',
        ]);

        $this->assertFalse(app(CatalogIntegrationAvailabilityService::class)->isAvailable());
    }

    public function test_expired_heartbeat_blocks_even_before_scheduler_marks_it_stale(): void
    {
        config(['services.integration_monitor.failure_alert_after_minutes' => 60]);
        $this->monitor([
            'status' => 'running',
            'last_heartbeat_at' => now()->subMinutes(61),
        ]);

        $this->assertFalse(app(CatalogIntegrationAvailabilityService::class)->isAvailable());
    }

    public function test_cart_add_route_is_rejected_when_catalog_is_unavailable(): void
    {
        $this->monitor([
            'status' => 'stale',
            'last_heartbeat_at' => now()->subDays(2),
            'error_code' => 'heartbeat_stale',
        ]);

        $response = $this->from('/produto/teste')->post(route('cart.add'), [
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $response->assertRedirect('/produto/teste');
        $response->assertSessionHas('catalog_purchase_blocked');
    }

    private function monitor(array $attributes): IntegrationMonitor
    {
        return IntegrationMonitor::query()->create($attributes + [
            'source' => 'catalog',
            'name' => 'Integração de produtos',
        ]);
    }
}
