<?php

namespace Tests\Feature;

use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Models\User;
use App\Models\WhatsappContact;
use App\Services\CatalogIntegrationAvailabilityService;
use App\Services\CuponService;
use App\Services\StoreControlService;
use App\Services\WhatsappWidgetService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CheckoutPauseCartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->withoutMiddleware(PreventRequestsDuringMaintenance::class);

        (require database_path('migrations/2026_04_07_151651_create_languages_table.php'))->up();
        (require database_path('migrations/2026_09_16_150000_add_checkout_pause_translations.php'))->up();
        foreach (DB::table('languages')->get() as $translation) {
            foreach (['pt_BR' => 'pt', 'en' => 'en', 'es' => 'es'] as $locale => $column) {
                app('translator')->addLines(['messages.'.$translation->key => $translation->{$column}], $locale);
            }
        }


        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->boolean('status');
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id');
            $table->boolean('status');
            $table->boolean('is_outlet');
            $table->integer('stock');
            $table->decimal('price', 12, 2);
            $table->string('name');
            $table->string('external_name')->nullable();
            $table->string('sku');
        });
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('product_id');
            $table->integer('quantity');
            $table->timestamps();
        });
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_default');
            $table->string('sign');
            $table->decimal('value', 12, 2);
        });
        DB::table('categories')->insert(['id' => 1, 'status' => 1]);
        DB::table('products')->insert([
            'id' => 10, 'category_id' => 1, 'status' => 1, 'is_outlet' => 0,
            'stock' => 5, 'price' => 12.5, 'name' => 'Producto & prueba', 'sku' => 'SKU-10',
        ]);
        DB::table('currencies')->insert(['id' => 1, 'is_default' => 1, 'sign' => 'US$', 'value' => 1]);
        $user = new User();
        $user->id = 7;
        $this->actingAs($user);
        $this->setAvailability(false, false);
        $this->mock(WhatsappWidgetService::class, function ($mock) {
            $mock->shouldReceive('configuration')->withArgs(fn ($request, $context) => $context === 'cart')
                ->andReturn(['contacts' => collect([new WhatsappContact(['phone' => '+595 981 123456'])])]);
        });
    }

    private function setAvailability(bool $checkout, bool $catalog): void
    {
        $this->instance(StoreControlService::class, new class($checkout) extends StoreControlService {
            public function __construct(private bool $checkout) {}
            public function settings(): array
            {
                return array_merge($this->defaults(), ['checkout_enabled' => $this->checkout]);
            }
        });
        $this->mock(CatalogIntegrationAvailabilityService::class, function ($mock) use ($catalog) {
            $mock->shouldReceive('isAvailable')->andReturn($catalog);
            $mock->shouldReceive('status')->andReturn(['available' => $catalog]);
        });
    }

    public function test_cart_can_add_and_increase_quantities_while_checkout_and_integration_are_unavailable(): void
    {
        $this->post(route('cart.add'), ['product_id' => 10, 'quantity' => 1])->assertRedirect();
        $this->assertDatabaseHas('carts', ['user_id' => 7, 'product_id' => 10, 'quantity' => 1]);

        $this->put(route('cart.update', 10), ['quantity' => 3])->assertRedirect();
        $this->assertDatabaseHas('carts', ['user_id' => 7, 'product_id' => 10, 'quantity' => 3]);
        $this->put(route('cart.update', 10), ['quantity' => 99])->assertRedirect();
        $this->assertDatabaseHas('carts', ['quantity' => 5]);
    }

    public function test_manual_pause_blocks_checkout_get_and_post_on_server(): void
    {
        $this->setAvailability(false, true);
        $this->get(route('checkout.index'))->assertRedirect(route('cart.view'))
            ->assertSessionHas('store_feature_blocked');
        $this->postJson(route('checkout.store'), [])->assertStatus(503)
            ->assertJson(['code' => 'store_feature_disabled', 'feature' => 'checkout']);
    }

    public function test_integration_failure_blocks_checkout_get_and_post_on_server(): void
    {
        $this->setAvailability(true, false);
        $this->get(route('checkout.index'))->assertRedirect(route('cart.view'))
            ->assertSessionHas('catalog_purchase_blocked');
        $this->postJson(route('checkout.store'), [])->assertStatus(503)
            ->assertJson(['code' => 'catalog_integration_unavailable']);
    }

    public function test_whatsapp_uses_configured_phone_and_cart_snapshot_without_mutations(): void
    {
        DB::table('carts')->insert(['user_id' => 7, 'product_id' => 10, 'quantity' => 2]);
        DB::table('carts')->insert(['user_id' => 8, 'product_id' => 10, 'quantity' => 4]);
        $before = DB::table('carts')->get()->toJson();
        $response = $this->get(route('cart.whatsapp'))->assertRedirect();
        $url = $response->headers->get('Location');
        $this->assertStringStartsWith('https://wa.me/595981123456?text=', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertStringContainsString('SKU: SKU-10', $query['text']);
        $this->assertStringContainsString('Cantidad: 2', $query['text']);
        $this->assertStringNotContainsString('Cantidad: 4', $query['text']);
        $this->assertStringContainsString('Precio unitario: US$ 12,50', $query['text']);
        $this->assertStringContainsString('Subtotal de productos: US$ 25,00', $query['text']);
        $this->assertStringContainsString('sujetos a confirmación', $query['text']);
        $this->assertStringNotContainsString('http', $query['text']);
        $this->assertSame($before, DB::table('carts')->get()->toJson());
        $this->assertDatabaseHas('products', ['id' => 10, 'stock' => 5]);
        // No orders table exists: creating an order would fail this request.
    }

    public function test_empty_cart_and_missing_contact_do_not_open_whatsapp(): void
    {
        $this->get(route('cart.whatsapp'))->assertRedirect(route('cart.view'))->assertSessionHas('error');
        DB::table('carts')->insert(['user_id' => 7, 'product_id' => 10, 'quantity' => 2]);
        $this->mock(WhatsappWidgetService::class, fn ($mock) => $mock->shouldReceive('configuration')->andReturn(['contacts' => collect()]));
        $this->get(route('cart.whatsapp'))->assertRedirect(route('cart.view'))->assertSessionHas('error');
        $this->assertDatabaseCount('carts', 1);
    }

    public function test_whatsapp_still_requires_login(): void
    {
        $this->app['auth']->forgetGuards();
        $this->getJson(route('cart.whatsapp'))->assertUnauthorized();
    }

    public function test_order_creation_rechecks_quantity_after_checkout_is_reenabled(): void
    {
        $this->setAvailability(true, true);
        DB::table('carts')->insert(['user_id' => 7, 'product_id' => 10, 'quantity' => 6]);
        $this->mock(CuponService::class, fn ($mock) => $mock->shouldReceive('resumoDoCarrinho')->andReturn([
            'subtotal' => 75, 'desconto' => 0, 'cupon' => null, 'total' => 75,
        ]));
        $this->get(route('checkout.whatsapp'))->assertRedirect(route('cart.view'))
            ->assertSessionHas('error', __('messages.checkout_pause_stock_changed'));
        $this->assertDatabaseHas('carts', ['quantity' => 6]);
    }

    public function test_payment_entry_points_remain_guarded_but_cart_and_callbacks_are_not_blocked(): void
    {
        $routes = app('router')->getRoutes();
        foreach (['checkout.index', 'checkout.store', 'checkout.bancard.v2', 'checkout.rendix.pix', 'checkout.rendix.pix.renew', 'checkout.deposito', 'checkout.deposito.submit', 'orders.deposit.submit'] as $name) {
            $middleware = $routes->getByName($name)->middleware();
            $this->assertContains('store.feature:checkout', $middleware, $name);
            $this->assertContains('catalog.healthy', $middleware, $name);
        }
        foreach (['cart.view', 'cart.add', 'cart.update', 'cart.remove', 'cart.whatsapp', 'user.abandoned-carts.restore', 'bancard.v2.callback', 'rendix.pix.webhook'] as $name) {
            $middleware = $routes->getByName($name)->middleware();
            $this->assertNotContains('catalog.healthy', $middleware, $name);
            $this->assertNotContains('store.feature:checkout', $middleware, $name);
        }
    }
}
