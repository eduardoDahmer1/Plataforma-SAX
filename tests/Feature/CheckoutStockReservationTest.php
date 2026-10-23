<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Cupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\BancardPaymentCompletionService;
use App\Services\BancardV2Service;
use App\Services\BusinessEventService;
use App\Services\CheckoutStockReservationService;
use App\Services\CuponService;
use App\Services\ReceiptService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class CheckoutStockReservationTest extends TestCase
{
    private CheckoutStockReservationService $reservations;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->boolean('status')->default(true);
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id');
            $table->boolean('status')->default(true);
            $table->boolean('is_outlet')->default(false);
            $table->integer('stock');
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });
        Schema::create('carts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('product_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
        Schema::create('cupons', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo');
            $table->unsignedInteger('usado')->default(0);
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('cupon_id')->nullable();
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->default('bancard_v2');
            $table->decimal('total', 12, 2)->default(0);
            $table->string('payment_currency')->nullable();
            $table->decimal('payment_amount', 14, 2)->nullable();
            $table->string('email')->nullable();
            $table->string('payment_response_code')->nullable();
            $table->string('payment_response_message')->nullable();
            $table->timestamp('payment_failed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('product_id');
            $table->unsignedInteger('quantity');
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });
        Schema::create('user_cupons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('cupon_id');
            $table->foreignId('order_id')->nullable();
            $table->decimal('desconto', 12, 2)->default(0);
            $table->timestamp('usado_em')->nullable();
            $table->timestamps();
        });
        Schema::create('checkout_stock_reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('product_id');
            $table->foreignId('order_id')->nullable();
            $table->unsignedInteger('quantity');
            $table->string('status', 20)->default('held');
            $table->timestamp('expires_at');
            $table->timestamp('committed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->string('release_reason', 60)->nullable();
            $table->timestamps();
        });

        DB::table('users')->insert(['id' => 7, 'name' => 'Cliente', 'email' => 'cliente@example.com']);
        DB::table('categories')->insert(['id' => 1, 'status' => true]);
        DB::table('products')->insert([
            'id' => 10,
            'category_id' => 1,
            'status' => true,
            'is_outlet' => false,
            'stock' => 5,
            'price' => 20,
        ]);
        DB::table('carts')->insert([
            'id' => 1,
            'user_id' => 7,
            'product_id' => 10,
            'quantity' => 2,
        ]);

        $this->user = User::query()->findOrFail(7);
        $this->reservations = app(CheckoutStockReservationService::class);
    }

    public function test_reserving_is_idempotent_and_leaving_checkout_restores_stock(): void
    {
        $cart = $this->cart();

        $this->reservations->reserveCart($this->user, $cart);
        $this->assertDatabaseHas('products', ['id' => 10, 'stock' => 3]);
        $this->assertDatabaseHas('checkout_stock_reservations', [
            'user_id' => 7,
            'product_id' => 10,
            'quantity' => 2,
            'status' => 'held',
        ]);

        $this->reservations->reserveCart($this->user, $this->cart());
        $this->assertDatabaseHas('products', ['id' => 10, 'stock' => 3]);

        $this->reservations->releaseCheckout($this->user);
        $this->assertDatabaseHas('products', ['id' => 10, 'stock' => 5]);
        $this->assertDatabaseHas('checkout_stock_reservations', [
            'user_id' => 7,
            'product_id' => 10,
            'status' => 'released',
            'release_reason' => 'checkout_left',
        ]);
    }

    public function test_payment_commits_without_decrementing_twice_and_refund_restores_once(): void
    {
        $this->reservations->reserveCart($this->user, $this->cart());
        $order = $this->order();

        $this->reservations->attachToOrder($this->user, $order);
        $this->reservations->commitOrder($order);
        $this->reservations->commitOrder($order);

        $this->assertDatabaseHas('products', ['id' => 10, 'stock' => 3]);
        $this->assertDatabaseHas('checkout_stock_reservations', [
            'order_id' => $order->id,
            'quantity' => 2,
            'status' => 'committed',
        ]);

        $this->reservations->restoreCommittedOrder($order, 'test_refund');
        $this->reservations->restoreCommittedOrder($order, 'test_refund');
        $this->assertDatabaseHas('products', ['id' => 10, 'stock' => 5]);
        $this->assertDatabaseHas('checkout_stock_reservations', [
            'order_id' => $order->id,
            'status' => 'restored',
            'release_reason' => 'test_refund',
        ]);
    }

    public function test_expired_order_releases_stock_and_coupon_and_cannot_be_reused(): void
    {
        DB::table('cupons')->insert(['id' => 3, 'codigo' => 'TESTE', 'usado' => 1]);
        $this->reservations->reserveCart($this->user, $this->cart());
        $order = $this->order(['cupon_id' => 3, 'discount' => 5]);
        DB::table('user_cupons')->insert([
            'user_id' => 7,
            'cupon_id' => 3,
            'order_id' => $order->id,
            'desconto' => 5,
            'usado_em' => now(),
        ]);
        $this->reservations->attachToOrder($this->user, $order);
        DB::table('checkout_stock_reservations')->where('order_id', $order->id)->update([
            'expires_at' => now()->subMinute(),
        ]);

        $this->assertSame(1, $this->reservations->expireDueReservations());
        $this->assertDatabaseHas('products', ['id' => 10, 'stock' => 5]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'failed',
            'payment_status' => 'failed',
            'payment_response_code' => 'reservation_expired',
        ]);
        $this->assertDatabaseHas('cupons', ['id' => 3, 'usado' => 0]);
        $this->assertDatabaseMissing('user_cupons', ['order_id' => $order->id]);
        $this->assertFalse($this->reservations->orderHasActiveReservation($order));
    }

    public function test_late_bancard_confirmation_is_idempotent_and_reaccounts_coupon(): void
    {
        Mail::fake();
        DB::table('cupons')->insert(['id' => 3, 'codigo' => 'TESTE', 'usado' => 1]);
        $this->reservations->reserveCart($this->user, $this->cart());
        $order = $this->order(['cupon_id' => 3, 'discount' => 5, 'email' => 'cliente@example.com']);
        DB::table('user_cupons')->insert([
            'user_id' => 7,
            'cupon_id' => 3,
            'order_id' => $order->id,
            'desconto' => 5,
            'usado_em' => now(),
        ]);
        $this->reservations->attachToOrder($this->user, $order);
        $this->reservations->releaseOrder($order, 'reservation_expired');

        $receipts = Mockery::mock(ReceiptService::class);
        $receipts->shouldReceive('issueForOrder')->once();
        $events = Mockery::mock(BusinessEventService::class);
        $completion = new BancardPaymentCompletionService(
            $receipts,
            $events,
            $this->reservations,
            app(CuponService::class),
        );

        $completion->markPaid($order->fresh());
        $completion->markPaid($order->fresh());
        $completion->markFailed(
            $order->fresh(),
            new BancardV2Service('https://bancard.example', 'public', 'private'),
            ['response_code' => '05'],
            'failed',
        );

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
            'payment_status' => 'paid',
            'payment_response_code' => '00',
        ]);
        $this->assertDatabaseHas('products', ['id' => 10, 'stock' => 3]);
        $this->assertDatabaseHas('checkout_stock_reservations', [
            'order_id' => $order->id,
            'quantity' => 2,
            'status' => 'committed',
        ]);
        $this->assertDatabaseHas('cupons', ['id' => 3, 'usado' => 1]);
        $this->assertDatabaseCount('user_cupons', 1);
    }

    public function test_coupon_session_expires_with_the_payment_window(): void
    {
        $cupons = app(CuponService::class);
        $coupon = new Cupon();
        $coupon->codigo = 'JANELA30';

        $cupons->aplicarNaSessao($coupon);
        $this->assertSame('JANELA30', session(CuponService::SESSION_KEY));

        $this->travel(CuponService::SESSION_MINUTES + 1)->minutes();
        $summary = $cupons->resumoDoCarrinho($this->user, collect());

        $this->assertNull(session(CuponService::SESSION_KEY));
        $this->assertNull(session(CuponService::SESSION_EXPIRES_KEY));
        $this->assertNull($summary['cupon']);
        $this->assertSame(0.0, $summary['desconto']);
    }

    public function test_legacy_paid_order_is_restored_only_once(): void
    {
        DB::table('products')->where('id', 10)->update(['stock' => 3]);
        $order = $this->order(['status' => 'paid', 'payment_status' => 'paid']);

        $this->reservations->restoreCommittedOrder($order, 'legacy_refund');
        $this->reservations->restoreCommittedOrder($order, 'legacy_refund');

        $this->assertDatabaseHas('products', ['id' => 10, 'stock' => 5]);
        $this->assertDatabaseHas('checkout_stock_reservations', [
            'order_id' => $order->id,
            'quantity' => 2,
            'status' => 'restored',
            'release_reason' => 'legacy_refund',
        ]);
    }

    public function test_bancard_confirmation_must_match_amount_and_currency(): void
    {
        $order = $this->order([
            'payment_currency' => 'PYG',
            'payment_amount' => 275000,
        ]);
        $completion = new BancardPaymentCompletionService(
            Mockery::mock(ReceiptService::class),
            Mockery::mock(BusinessEventService::class),
            $this->reservations,
            app(CuponService::class),
        );

        $this->assertTrue($completion->confirmationMatchesOrder($order, [
            'currency' => 'PYG',
            'amount' => '275000.00',
        ]));
        $this->assertFalse($completion->confirmationMatchesOrder($order, [
            'currency' => 'USD',
            'amount' => '275000.00',
        ]));
        $this->assertFalse($completion->confirmationMatchesOrder($order, [
            'currency' => 'PYG',
            'amount' => '274999.00',
        ]));
    }

    private function cart()
    {
        return Cart::query()->with('product')->where('user_id', 7)->get();
    }

    private function order(array $attributes = []): Order
    {
        $order = Order::query()->create(array_merge([
            'user_id' => 7,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'bancard_v2',
            'total' => 40,
        ], $attributes));

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => 10,
            'quantity' => 2,
            'price' => 20,
        ]);

        return $order;
    }
}
