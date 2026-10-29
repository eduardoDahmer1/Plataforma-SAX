<?php

namespace Tests\Feature;

use App\Http\Controllers\CheckoutController;
use App\Models\DhlSetting;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\CuponService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DhlManualShippingModeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Schema::create('dhl_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('environment')->default('sandbox');
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('account_number')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_default')->default(true);
            $table->string('name')->default('USD');
            $table->string('sign')->default('US$');
            $table->decimal('value', 12, 4)->default(1);
            $table->timestamps();
        });
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('maintenance')->default(false);
            $table->string('store_profile')->default('stage');
            foreach ([
                'cart_enabled', 'checkout_enabled', 'add_to_cart_enabled',
                'deposit_enabled', 'bancard_enabled', 'pix_enabled', 'whatsapp_enabled',
                'geonames_enabled', 'header_categories_enabled', 'header_institucional_enabled',
                'header_bridal_enabled', 'header_palace_enabled', 'header_cafe_enabled',
                'header_blog_enabled', 'header_contact_enabled', 'header_guide_enabled',
                'footer_categories_enabled', 'footer_institucional_enabled', 'footer_bridal_enabled',
                'footer_palace_enabled', 'footer_cafe_enabled', 'footer_blog_enabled',
                'footer_contact_enabled', 'footer_guide_enabled',
            ] as $field) {
                $table->boolean($field)->default(true);
            }
            $table->timestamps();
        });

        DhlSetting::query()->create(['enabled' => false, 'environment' => 'production']);
    }

    public function test_disabled_dhl_returns_manual_shipping_without_requesting_a_quote(): void
    {
        $user = new User;
        $user->id = 77;
        $user->user_type = User::TYPE_CUSTOMER;
        $this->actingAs($user);

        $this->mock(CuponService::class, function ($mock): void {
            $mock->shouldReceive('resumoDoCarrinho')->once()->andReturn([
                'subtotal' => 125,
                'desconto' => 25,
                'cupon' => null,
                'total' => 100,
                'aviso' => null,
            ]);
        });
        $request = Request::create('/checkout/calcular-frete', 'POST', [
            'country' => 'brasil',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);

        /** @var CheckoutController $controller */
        $controller = app(CheckoutController::class);
        $response = $controller->ajaxCalcularFrete($request);
        $payload = $response->getData(true);

        $this->assertTrue($payload['manual_shipping']);
        $this->assertSame(0, $payload['frete']);
        $this->assertContains($payload['frete_formatado'], ['A combinar', 'A coordinar', 'To be arranged']);
        $this->assertStringContainsString('SAX', $payload['message']);
    }

    public function test_master_admin_can_disable_only_dhl_from_store_controls(): void
    {
        DhlSetting::query()->firstOrFail()->forceFill(['enabled' => true])->save();
        SystemSetting::query()->create([
            'maintenance' => false,
            'store_profile' => 'sax',
            'cart_enabled' => true,
            'checkout_enabled' => true,
        ]);

        $admin = new User;
        $admin->id = 91;
        $admin->user_type = User::TYPE_ADMIN_MASTER;
        $this->actingAs($admin);

        $payload = collect((new \App\Services\StoreControlService)->defaults())
            ->except('store_profile')
            ->mapWithKeys(fn ($value, string $field): array => [$field => $value ? '1' : '0'])
            ->all();

        $this->put(route('admin.store-controls.update'), $payload + [
            'store_profile' => 'sax',
            'dhl_enabled' => '0',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertFalse((bool) DhlSetting::query()->value('enabled'));
        $this->assertTrue((bool) SystemSetting::query()->value('checkout_enabled'));
        $this->assertTrue((bool) SystemSetting::query()->value('cart_enabled'));
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
