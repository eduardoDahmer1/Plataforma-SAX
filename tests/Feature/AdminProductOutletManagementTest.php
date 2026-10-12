<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ProductFeedRefreshService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class AdminProductOutletManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->withoutMiddleware();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedTinyInteger('user_type')->default(User::TYPE_ADMIN_MASTER);
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name')->nullable();
            $table->string('external_name')->nullable();
            $table->decimal('price', 10, 2)->default(10);
            $table->integer('stock')->default(5);
            $table->boolean('status')->default(true);
            $table->boolean('is_outlet')->default(false);
            $table->boolean('status_before_outlet')->nullable();
            $table->timestamp('outlet_at')->nullable();
            $table->unsignedBigInteger('outlet_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('admin_edited_at')->nullable();
            $table->json('highlights')->nullable();
            $table->string('photo')->nullable();
            $table->json('gallery')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
        });
        Schema::create('product_outlet_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('product_name')->nullable();
            $table->string('action');
            $table->boolean('previous_status')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        $user = User::forceCreate([
            'name' => 'Editora SAX', 'email' => 'editora@example.com',
            'password' => 'secret', 'user_type' => User::TYPE_ADMIN_MASTER,
        ]);
        $this->actingAs($user);
        $feed = Mockery::mock(ProductFeedRefreshService::class);
        $feed->shouldReceive('markDirty')->zeroOrMoreTimes();
        $this->app->instance(ProductFeedRefreshService::class, $feed);
    }

    private function product(string $sku, array $attributes = []): int
    {
        return DB::table('products')->insertGetId(array_merge([
            'sku' => $sku,
            'name' => 'Produto '.$sku,
            'price' => 100,
            'stock' => 4,
            'status' => true,
            'is_outlet' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));
    }

    public function test_outlet_batch_records_when_and_who_and_restores_previous_status(): void
    {
        $id = $this->product('SKU-OUTLET');
        DB::table('carts')->insert(['product_id' => $id]);

        $this->put(route('admin.products.outlet.update'), [
            'skus' => 'SKU-OUTLET', 'action' => 'outlet',
        ])->assertRedirect(route('admin.products.outlet.form'));

        $this->assertDatabaseHas('products', [
            'id' => $id, 'is_outlet' => true, 'status' => false, 'status_before_outlet' => true,
            'outlet_by' => auth()->id(),
        ]);
        $this->assertNotNull(DB::table('products')->where('id', $id)->value('outlet_at'));
        $this->assertDatabaseMissing('carts', ['product_id' => $id]);
        $this->assertDatabaseHas('product_outlet_events', [
            'product_id' => $id, 'user_id' => auth()->id(), 'action' => 'outlet',
        ]);

        $this->put(route('admin.products.outlet.update'), [
            'skus' => 'SKU-OUTLET', 'action' => 'restore',
        ])->assertRedirect(route('admin.products.outlet.form'));

        $this->assertDatabaseHas('products', [
            'id' => $id, 'is_outlet' => false, 'status' => true,
            'status_before_outlet' => null, 'outlet_at' => null, 'outlet_by' => null,
        ]);
        $this->assertDatabaseCount('product_outlet_events', 2);
    }

    public function test_bulk_selection_can_deactivate_and_manage_highlights(): void
    {
        $first = $this->product('SKU-1');
        $second = $this->product('SKU-2');

        $this->post(route('admin.products.bulkAction'), [
            'product_ids' => [$first, $second], 'action' => 'deactivate',
        ])->assertRedirect();
        $this->assertSame(0, DB::table('products')->whereIn('id', [$first, $second])->where('status', true)->count());

        $this->post(route('admin.products.bulkAction'), [
            'product_ids' => [$first, $second], 'action' => 'feature',
        ])->assertRedirect();
        foreach (DB::table('products')->whereIn('id', [$first, $second])->pluck('highlights') as $highlights) {
            $this->assertSame('1', data_get(json_decode($highlights, true), 'destaque'));
        }
    }

    public function test_bulk_action_rejects_an_empty_selection(): void
    {
        $this->from(route('admin.products.index'))->post(route('admin.products.bulkAction'), [
            'product_ids' => [], 'action' => 'deactivate',
        ])->assertRedirect(route('admin.products.index'))->assertSessionHasErrors('product_ids');
    }
}
