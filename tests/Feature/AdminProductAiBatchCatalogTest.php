<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAiPreparation;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductAiBatchCatalogTest extends TestCase
{
    use RefreshDatabase;

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
}
