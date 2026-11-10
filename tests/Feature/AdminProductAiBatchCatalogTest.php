<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CategoriasFilhas;
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

    public function test_batch_status_and_page_expose_saved_source_links(): void
    {
        $admin = User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]);
        $product = Product::create(['sku' => 'WITH-SOURCE', 'name' => 'Producto', 'price' => 10, 'status' => 0]);
        $batch = \App\Models\ProductAiBatch::create(['source_type' => 'paste', 'status' => 'completed', 'eligible_count' => 1]);
        $batch->items()->create(['product_id' => $product->id, 'submitted_sku' => $product->sku, 'status' => 'completed']);
        ProductAiPreparation::create(['product_id' => $product->id, 'status' => 'completed', 'sources' => [
            ['url' => 'https://manufacturer.example/product', 'title' => 'Fabricante'],
        ]]);
        $this->actingAs($admin)->getJson(route('admin.products.ai-batches.status', $batch))
            ->assertOk()->assertJsonPath('items.0.sources.0.url', 'https://manufacturer.example/product');
        $page = app(\App\Http\Controllers\Admin\ProductAiBatchController::class)->show($batch);
        $sources = $page->getData()['batch']->items->first()->product->aiPreparation->sources;
        $html = view('admin.products.ai-batches.sources', compact('sources'))->render();
        $this->assertStringContainsString('Fuentes consultadas', $html);
        $this->assertStringContainsString('href="https://manufacturer.example/product"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
    }

    public function test_batch_consultation_uses_active_guide_contacts_and_source_variants(): void
    {
        $location = \App\Models\ContactGuideLocation::create(['name' => 'SAX Department Store Test', 'city' => 'CDE', 'active' => true]);
        $brandEntry = \App\Models\ContactGuideEntry::create(['location_id' => $location->id, 'sector' => 'Bolsas', 'floor' => '1', 'phone' => '+595 981 123456', 'brands' => ['Dolce & Gabbana'], 'active' => true]);
        $taxonomyEntry = \App\Models\ContactGuideEntry::create(['location_id' => $location->id, 'sector' => 'Perfumaria', 'floor' => '1', 'phone' => '+595 981 654321', 'brands' => [], 'active' => true]);
        \App\Models\ContactGuideEntry::create(['location_id' => $location->id, 'sector' => 'Oculto', 'floor' => '1', 'phone' => '999', 'active' => false]);
        $brand = Brand::create(['name' => 'Dolce & Gabbana', 'slug' => 'dolce-gabbana', 'status' => 1]);
        $category = Category::create(['name' => 'Perfumes', 'slug' => 'perfumes', 'status' => 1]);
        $product = Product::create(['sku' => 'VAR-1', 'name' => 'Bolsa chica', 'price' => 10, 'brand_id' => $brand->id, 'category_id' => $category->id]);
        Product::create(['sku' => 'VAR-2', 'name' => 'Bolsa grande', 'price' => 10, 'brand_id' => $brand->id, 'category_id' => $category->id]);
        $batch = \App\Models\ProductAiBatch::create(['source_type' => 'paste', 'status' => 'completed']);
        $batch->items()->create(['product_id' => $product->id, 'submitted_sku' => 'VAR-1', 'source_skus' => ['VAR-1', 'VAR-2'], 'status' => 'completed']);

        $data = app(\App\Http\Controllers\Admin\ProductAiBatchController::class)->show($batch)->getData();
        $this->assertTrue($data['guideEntries']->contains(fn ($entry) => $entry->sector === 'Bolsas' && $entry->phone === '+595 981 123456'));
        $this->assertFalse($data['guideEntries']->contains(fn ($entry) => $entry->sector === 'Oculto'));
        $this->assertSame('Bolsa grande', $data['consultationProducts']->get('var-2')->name);
        $this->assertSame('Dolce & Gabbana', $data['consultationProducts']->get('var-2')->brand->name);
        $this->assertSame('brand', $data['consultationProducts']->get('var-2')->guide_matches[(string) $brandEntry->id]);
        $this->assertSame('taxonomy', $data['consultationProducts']->get('var-2')->guide_matches[(string) $taxonomyEntry->id]);
    }

    public function test_consultation_groups_guide_locations_into_buildings_and_labels_repeated_sectors(): void
    {
        $locations = \App\Models\ContactGuideLocation::all()->keyBy('name');
        $expected = [
            'SAX Department Store' => ['department', 'SAX Department Store'],
            'Shopping Dubai' => ['dubai', 'Shopping Dubai'],
            'Distrito Perseverancia · Guembe' => ['distrito', 'Guembe'],
            'Distrito Perseverancia · Casona' => ['distrito', 'Casona'],
        ];
        $entries = [];
        foreach ($expected as $locationName => $destination) {
            $entry = \App\Models\ContactGuideEntry::create([
                'location_id' => $locations[$locationName]->id,
                'sector' => 'Sector repetido', 'floor' => 'Piso 1',
                'phone' => '+595 981 555555', 'brands' => ['Marca sugerida'], 'active' => true,
            ]);
            $entries[$entry->id] = $destination;
        }
        $hidden = \App\Models\ContactGuideEntry::create([
            'location_id' => $locations['Shopping Dubai']->id,
            'sector' => 'Inactivo', 'floor' => 'PB', 'active' => false,
        ]);
        $closed = \App\Models\ContactGuideLocation::create(['name' => 'SAX Shopping Dubai inactivo', 'city' => 'PJC', 'active' => false]);
        $closedEntry = \App\Models\ContactGuideEntry::create([
            'location_id' => $closed->id, 'sector' => 'Cerrado', 'floor' => 'PB', 'active' => true,
        ]);
        $batch = \App\Models\ProductAiBatch::create(['source_type' => 'paste', 'status' => 'completed']);
        $contacts = app(\App\Http\Controllers\Admin\ProductAiBatchController::class)->show($batch)->getData()['guideContacts']->keyBy('id');

        foreach ($entries as $id => [$building, $place]) {
            $contact = $contacts->get($id);
            $this->assertSame($building, $contact['building']);
            $this->assertSame('Sector repetido · '.$place.' · Piso 1', $contact['label']);
            $this->assertSame('+595 981 555555', $contact['phone']);
            $this->assertSame(['Marca sugerida'], $contact['brands']);
            $this->assertStringNotContainsString($contact['phone'], $contact['label']);
        }
        $this->assertFalse($contacts->has($hidden->id));
        $this->assertFalse($contacts->has($closedEntry->id));
    }

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
        $childcategory = CategoriasFilhas::create([
            'name' => 'Categoría hija de prueba',
            'slug' => 'categoria-hija-prueba',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
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
            'childcategory_id' => $childcategory->id,
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
                'childcategory_id' => $childcategory->id,
            ]
        ));

        $response
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.sku', 'SKU-CATALOG-001')
            ->assertJsonPath('data.0.brand', 'Marca de prueba')
            ->assertJsonPath('data.0.category', 'Categoría de prueba')
            ->assertJsonPath('data.0.subcategory', 'Subcategoría de prueba')
            ->assertJsonPath('data.0.childcategory', 'Categoría hija de prueba')
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

    public function test_catalog_filters_products_never_edited_without_image_or_with_incomplete_content(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]));
        Product::create([
            'sku' => 'NEEDS-WORK', 'name' => 'Producto incompleto', 'price' => 10,
            'description' => null, 'photo' => null, 'gallery' => [], 'admin_edited_at' => null,
        ]);
        Product::create([
            'sku' => 'READY', 'name' => 'Producto completo', 'price' => 10,
            'description' => 'Descripción lista', 'photo' => 'products/ready.webp', 'gallery' => [],
            'admin_edited_at' => '2026-11-10 10:00:00',
        ]);

        foreach ([
            ['editing_filter' => 'never_edited'],
            ['image_filter' => 'without'],
            ['content_filter' => 'incomplete'],
        ] as $filter) {
            $this->getJson(route('admin.products.ai-batches.catalog-products', $filter))
                ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.sku', 'NEEDS-WORK');
        }
    }

    public function test_priority_sort_uses_editing_need_and_views_and_can_select_all_filtered_results(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => User::TYPE_ADMIN_MASTER]));
        Product::create([
            'sku' => 'POPULAR', 'name' => 'Camiseta popular', 'price' => 10, 'description' => '',
            'views' => 90, 'admin_edited_at' => null, 'created_at' => '2026-11-01',
        ]);
        Product::create([
            'sku' => 'RECENT', 'name' => 'Producto reciente', 'price' => 10, 'description' => '',
            'views' => 2, 'admin_edited_at' => null, 'created_at' => '2026-11-10',
        ]);
        Product::create([
            'sku' => 'EDITED', 'name' => 'Producto editado', 'price' => 10, 'description' => '',
            'views' => 500, 'admin_edited_at' => '2026-11-10 12:00:00',
        ]);

        $this->getJson(route('admin.products.ai-batches.catalog-products', ['sort_by' => 'priority']))
            ->assertOk()->assertJsonPath('data.0.sku', 'POPULAR')
            ->assertJsonPath('data.0.never_edited', true)
            ->assertJsonPath('data.0.views', 90);

        $this->getJson(route('admin.products.ai-batches.catalog-products', [
            'editing_filter' => 'never_edited', 'select_all' => 1,
        ]))->assertOk()->assertJsonPath('meta.selection', true)->assertJsonPath('meta.total', 2)->assertJsonCount(2, 'data');
    }
}
