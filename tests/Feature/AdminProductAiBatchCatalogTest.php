<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAiPreparation;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\Support\UsesProductAiDatabase;
use Tests\TestCase;

class AdminProductAiBatchCatalogTest extends TestCase
{
    use UsesProductAiDatabase;

    public function test_non_admin_cannot_access_the_catalog_endpoint(): void
    {
        $customer = User::factory()->create(['user_type' => User::TYPE_CUSTOMER]);

        $this->actingAs($customer)
            ->getJson(route('admin.products.ai-batches.catalog-products'))
            ->assertForbidden();
    }

    public function test_admin_can_search_and_filter_catalog_products(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $brand = Brand::create(['name' => 'Marca de prueba', 'slug' => 'marca-prueba', 'status' => 1]);
        $category = Category::create(['name' => 'Categoría de prueba', 'slug' => 'categoria-prueba', 'status' => 1]);
        $subcategory = Subcategory::create([
            'name' => 'Subcategoría de prueba',
            'category_id' => $category->id,
        ]);

        Product::create([
            'sku' => 'SKU-CATALOG-001',
            'name' => 'Producto visible',
            'external_name' => 'Producto externo visible',
            'ref_code' => 'REF-001',
            'price' => 10,
            'status' => 0,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'gallery' => [],
        ]);

        Product::create([
            'sku' => 'SKU-CATALOG-002',
            'name' => 'Otro producto',
            'price' => 10,
            'status' => 0,
            'gallery' => [],
        ]);

        $response = $this->actingAs($admin)->getJson(route(
            'admin.products.ai-batches.catalog-products',
            [
                'search' => 'REF-001',
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'subcategory_id' => $subcategory->id,
            ]
        ));

        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.sku', 'SKU-CATALOG-001')
            ->assertJsonPath('data.0.brand', 'Marca de prueba')
            ->assertJsonPath('data.0.category', 'Categoría de prueba')
            ->assertJsonPath('data.0.subcategory', 'Subcategoría de prueba')
            ->assertJsonPath('data.0.ai_status', 'pending');
    }

    public function test_catalog_endpoint_paginates_results(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);

        for ($index = 1; $index <= 21; $index++) {
            Product::create([
                'sku' => sprintf('SKU-PAGE-%03d', $index),
                'name' => sprintf('Producto %03d', $index),
                'price' => 10,
                'status' => 0,
                'gallery' => [],
            ]);
        }

        $response = $this->actingAs($admin)->getJson(route(
            'admin.products.ai-batches.catalog-products',
            ['per_page' => 20, 'page' => 2]
        ));

        $response
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 21)
            ->assertJsonCount(1, 'data');
    }

    public function test_catalog_endpoint_filters_ai_preparation_states(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);

        $prepared = Product::create([
            'sku' => 'SKU-AI-PREPARED',
            'name' => 'Producto preparado',
            'price' => 10,
            'status' => 0,
            'photo' => 'products/prepared.webp',
            'gallery' => [],
        ]);
        Storage::disk('public')->put('products/prepared.webp', 'image');
        ProductAiPreparation::create([
            'product_id' => $prepared->id,
            'status' => ProductAiPreparation::STATUS_COMPLETED,
        ]);

        $missingPhoto = Product::create([
            'sku' => 'SKU-AI-MISSING',
            'name' => 'Producto sin foto',
            'price' => 10,
            'status' => 0,
            'gallery' => [],
        ]);
        ProductAiPreparation::create([
            'product_id' => $missingPhoto->id,
            'status' => ProductAiPreparation::STATUS_COMPLETED,
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.products.ai-batches.catalog-products', [
                'ai_preparation_filter' => 'prepared',
            ]))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.sku', 'SKU-AI-PREPARED');

        $this->actingAs($admin)
            ->getJson(route('admin.products.ai-batches.catalog-products', [
                'ai_preparation_filter' => 'missing_photo',
            ]))
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.sku', 'SKU-AI-MISSING');
    }

    public function test_extended_filters_combine_and_preserve_zero_boundaries(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]));
        Product::create(['sku' => 'MATCH', 'name' => 'Producto elegido', 'price' => 25, 'stock' => 0,
            'status' => 0, 'is_outlet' => true, 'description' => '', 'ref_code' => 'ABC',
            'created_at' => '2026-09-09 23:59:59']);
        Product::create(['sku' => 'OTHER', 'price' => 80, 'stock' => 10, 'status' => 1]);
        \Illuminate\Support\Facades\DB::table('products')->where('sku', 'MATCH')->update(['created_at' => '2026-09-09 23:59:59']);
        $this->getJson(route('admin.products.ai-batches.catalog-products', [
            'publication' => 'inactive', 'stock_filter' => 'out_of_stock', 'stock_min' => 0, 'stock_max' => 0,
            'price_min' => 20, 'price_max' => 30, 'description_filter' => 'without',
            'reference_filter' => 'with', 'outlet_filter' => 'outlet', 'product_type' => 'parent',
            'created_from' => '2026-09-09', 'created_to' => '2026-09-09',
        ]))->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.sku', 'MATCH');
    }

    public function test_upper_bounds_work_without_lower_bounds_and_sorting_is_applied(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]));
        Product::create(['sku' => 'LOW', 'price' => 10, 'stock' => 2, 'created_at' => '2026-09-08']);
        Product::create(['sku' => 'HIGH', 'price' => 20, 'stock' => 4, 'created_at' => '2026-09-09']);
        \Illuminate\Support\Facades\DB::table('products')->where('sku', 'LOW')->update(['created_at' => '2026-09-08 00:00:00']);
        \Illuminate\Support\Facades\DB::table('products')->where('sku', 'HIGH')->update(['created_at' => '2026-09-09 00:00:00']);
        $this->getJson(route('admin.products.ai-batches.catalog-products', [
            'stock_max' => 5, 'price_max' => 20, 'created_to' => '2026-09-09', 'sort_by' => 'price_high', 'per_page' => 50,
        ]))->assertOk()->assertJsonPath('meta.total', 2)->assertJsonPath('meta.per_page', 50)->assertJsonPath('data.0.sku', 'HIGH');
    }

    public function test_each_additional_filter_excludes_non_matching_products(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]));
        $parent = Product::create(['sku' => 'PARENT', 'price' => 10, 'stock' => 0, 'status' => 0,
            'description' => '  ', 'ref_code' => null, 'created_at' => '2026-09-01']);
        Product::create(['sku' => 'CHILD', 'price' => 50, 'stock' => 8, 'status' => 1, 'is_outlet' => true,
            'description' => 'Descripción', 'ref_code' => 'REF', 'parent_id' => $parent->id, 'created_at' => '2026-09-09']);
        \Illuminate\Support\Facades\DB::table('products')->where('sku', 'PARENT')->update(['created_at' => '2026-09-01 00:00:00']);
        \Illuminate\Support\Facades\DB::table('products')->where('sku', 'CHILD')->update(['created_at' => '2026-09-09 00:00:00']);
        foreach ([
            ['publication' => 'active'], ['stock_filter' => 'in_stock'], ['stock_min' => 1],
            ['price_min' => 30], ['description_filter' => 'with'], ['reference_filter' => 'with'],
            ['outlet_filter' => 'outlet'], ['product_type' => 'child'], ['created_from' => '2026-09-09'],
        ] as $filter) {
            $this->getJson(route('admin.products.ai-batches.catalog-products', $filter))
                ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.sku', 'CHILD');
        }
        foreach ([
            ['publication' => 'inactive'], ['stock_filter' => 'out_of_stock'], ['stock_max' => 0],
            ['price_max' => 10], ['description_filter' => 'without'], ['reference_filter' => 'without'],
            ['outlet_filter' => 'regular'], ['product_type' => 'parent'], ['created_to' => '2026-09-01'],
        ] as $filter) {
            $this->getJson(route('admin.products.ai-batches.catalog-products', $filter))
                ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.sku', 'PARENT');
        }
    }

    public function test_invalid_filters_and_reversed_ranges_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]));
        foreach ([
            ['price_min' => 30, 'price_max' => 20], ['stock_min' => 10, 'stock_max' => 0],
            ['created_from' => '2026-09-09', 'created_to' => '2026-09-08'],
            ['stock_min' => -1], ['sort_by' => 'arbitrary_sql'], ['publication' => 'invalid'],
        ] as $filters) {
            $this->getJson(route('admin.products.ai-batches.catalog-products', $filters))->assertUnprocessable();
        }
    }
}
