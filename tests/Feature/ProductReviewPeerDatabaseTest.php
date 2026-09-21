<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ProductControllerAdmin;
use App\Models\Product;
use App\Services\OpticalProductSyncService;
use App\Services\StoreControlService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class ProductReviewPeerDatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.catalog_peer' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
            'catalog-sync.connection' => 'catalog_peer',
        ]);
        DB::purge('sqlite');
        DB::purge('catalog_peer');

        foreach (['sqlite', 'catalog_peer'] as $connection) {
            Schema::connection($connection)->create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
            });
            Schema::connection($connection)->create('products', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('external_name')->nullable();
                $table->string('sku')->nullable();
                $table->string('ref_code')->nullable();
                $table->decimal('price', 10, 2);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamp('admin_edited_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_platform_report_uses_the_latest_edit_and_the_editor_from_the_correct_database(): void
    {
        $this->setOpticalProfile(false);
        DB::table('users')->insert(['id' => 1, 'name' => 'Ana Plataforma']);
        DB::connection('catalog_peer')->table('users')->insert(['id' => 7, 'name' => 'Bia Ótica']);

        DB::table('products')->insert([
            ['id' => 10, 'sku' => 'A', 'price' => 10, 'updated_by' => 1, 'admin_edited_at' => '2026-09-20 10:00:00'],
            ['id' => 11, 'sku' => 'B', 'price' => 10, 'updated_by' => 1, 'admin_edited_at' => '2026-09-21 14:00:00'],
        ]);
        DB::connection('catalog_peer')->table('products')->insert([
            ['id' => 100, 'sku' => 'A', 'price' => 10, 'updated_by' => 7, 'admin_edited_at' => '2026-09-21 12:00:00'],
            ['id' => 101, 'sku' => 'B', 'price' => 10, 'updated_by' => 7, 'admin_edited_at' => '2026-09-20 09:00:00'],
            ['id' => 12, 'sku' => 'C', 'price' => 10, 'updated_by' => 7, 'admin_edited_at' => '2026-09-21 11:00:00'],
        ]);

        [$products, $available] = $this->reviewProducts();

        $this->assertTrue($available);
        $this->assertSame([11, 100, 12], $products->pluck('id')->all());
        $this->assertSame('Ana Plataforma', $products->firstWhere('id', 11)->editor_label);
        $this->assertSame('Ótica · Bia Ótica', $products->firstWhere('id', 100)->editor_label);
        $this->assertSame('Ótica · Bia Ótica', $products->firstWhere('id', 12)->editor_label);

        $this->setOpticalProfile(true);
        [$opticalReport, $available] = $this->reviewProducts();
        $this->assertTrue($available);
        $this->assertSame([11, 10], $opticalReport->pluck('id')->all());
    }

    public function test_sync_preserves_the_peer_store_admin_editor_and_edit_date(): void
    {
        DB::connection('catalog_peer')->table('products')->insert([
            'id' => 10,
            'sku' => 'A',
            'price' => 10,
            'updated_by' => 7,
            'admin_edited_at' => '2026-09-20 09:00:00',
        ]);

        $source = (new Product())->forceFill([
            'id' => 10,
            'sku' => 'A',
            'price' => 20,
            'updated_by' => 1,
            'admin_edited_at' => '2026-09-21 14:00:00',
        ]);
        $method = new ReflectionMethod(OpticalProductSyncService::class, 'syncProduct');
        $method->invoke(app(OpticalProductSyncService::class), DB::connection('catalog_peer'), $source);

        $peer = DB::connection('catalog_peer')->table('products')->find(10);
        $this->assertSame(20.0, (float) $peer->price);
        $this->assertSame(7, $peer->updated_by);
        $this->assertSame('2026-09-20 09:00:00', $peer->admin_edited_at);
    }

    private function reviewProducts(): array
    {
        $method = new ReflectionMethod(ProductControllerAdmin::class, 'reviewProducts');

        return $method->invoke(
            new ProductControllerAdmin(),
            Carbon::parse('2026-09-20 00:00:00'),
            Carbon::parse('2026-09-21 23:59:59')
        );
    }

    private function setOpticalProfile(bool $isOtica): void
    {
        $service = Mockery::mock(StoreControlService::class);
        $service->shouldReceive('isOtica')->andReturn($isOtica);
        $this->app->instance(StoreControlService::class, $service);
    }
}
