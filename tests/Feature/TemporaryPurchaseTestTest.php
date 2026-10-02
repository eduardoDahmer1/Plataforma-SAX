<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Services\CatalogIntegrationAvailabilityService;
use App\Services\StoreControlService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TemporaryPurchaseTestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-09-28 16:00:00');
        Cache::flush();
        Schema::dropIfExists('system_settings');
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('maintenance')->default(false);
            $table->string('store_profile')->default('stage');
            $table->boolean('cart_enabled')->default(false);
            $table->boolean('checkout_enabled')->default(false);
            $table->boolean('add_to_cart_enabled')->default(false);
            $table->boolean('deposit_enabled')->default(false);
            $table->boolean('bancard_enabled')->default(false);
            $table->boolean('pix_enabled')->default(false);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->boolean('geonames_enabled')->default(false);
            $table->timestamp('purchase_test_until')->nullable();
            $table->unsignedBigInteger('purchase_test_activated_by')->nullable();
            $table->timestamps();
        });

        SystemSetting::query()->create([
            'maintenance' => false,
            'store_profile' => 'stage',
            'cart_enabled' => false,
            'checkout_enabled' => false,
            'add_to_cart_enabled' => false,
            'deposit_enabled' => false,
            'bancard_enabled' => false,
            'pix_enabled' => false,
            'whatsapp_enabled' => false,
            'geonames_enabled' => false,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Cache::flush();
        parent::tearDown();
    }

    public function test_window_enables_the_complete_purchase_flow_without_changing_manual_controls(): void
    {
        $controls = new StoreControlService();

        $this->assertFalse($controls->enabled('checkout'));
        $status = $controls->activateTemporaryPurchaseTest(91);

        $this->assertTrue($status['active']);
        $this->assertSame(300, $status['remaining_seconds']);
        $this->assertSame(91, $status['activated_by']);

        foreach (['cart', 'add_to_cart', 'checkout', 'deposit', 'bancard', 'pix', 'whatsapp', 'geonames'] as $feature) {
            $this->assertTrue($controls->enabled($feature), $feature);
        }

        $this->assertFalse($controls->manualSettings()['checkout_enabled']);
        $this->assertDatabaseHas('system_settings', [
            'checkout_enabled' => false,
            'purchase_test_activated_by' => 91,
        ]);
    }

    public function test_window_bypasses_an_unavailable_catalog_only_until_it_expires(): void
    {
        $controls = new StoreControlService();
        $controls->activateTemporaryPurchaseTest(91);

        $availability = new CatalogIntegrationAvailabilityService($controls);
        $status = $availability->status();

        $this->assertTrue($status['available']);
        $this->assertFalse($status['actual_available']);
        $this->assertTrue($status['temporary_test']);

        Carbon::setTestNow(now()->addMinutes(5)->addSecond());
        $expiredControls = new StoreControlService();
        $expiredAvailability = new CatalogIntegrationAvailabilityService($expiredControls);

        $this->assertFalse($expiredControls->temporaryPurchaseTestActive());
        $this->assertFalse($expiredControls->enabled('checkout'));
        $this->assertFalse($expiredAvailability->isAvailable());
    }

    public function test_window_can_be_ended_before_the_deadline(): void
    {
        $controls = new StoreControlService();
        $controls->activateTemporaryPurchaseTest(91);
        $controls->deactivateTemporaryPurchaseTest();

        $this->assertFalse($controls->temporaryPurchaseTestActive());
        $this->assertNull(SystemSetting::query()->value('purchase_test_until'));
        $this->assertNull(SystemSetting::query()->value('purchase_test_activated_by'));
    }
    public function test_master_admin_can_activate_and_end_the_window_through_the_panel_routes(): void
    {
        $admin = new \App\Models\User();
        $admin->id = 91;
        $admin->user_type = \App\Models\User::TYPE_ADMIN_MASTER;
        $this->actingAs($admin);

        $this->post(route('admin.store-controls.temporary-test.activate'))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertTrue((new StoreControlService())->temporaryPurchaseTestActive());

        $this->delete(route('admin.store-controls.temporary-test.deactivate'))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertFalse((new StoreControlService())->temporaryPurchaseTestActive());
    }
}
