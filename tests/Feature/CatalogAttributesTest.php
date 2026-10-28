<?php

namespace Tests\Feature;

use App\Jobs\GenerateProductAiPreparation;
use App\Models\CatalogAttribute;
use App\Models\Product;
use App\Models\ProductAiBatch;
use App\Models\ProductAiPreparation;
use App\Models\User;
use App\Services\CatalogAttributeService;
use App\Services\CatalogAttributeSyncService;
use App\Services\OpenAICatalogService;
use App\Services\ProductAiBatchDraftBuilder;
use App\Services\ProductAiProposalApplier;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\Support\UsesProductAiDatabase;
use Tests\TestCase;

class CatalogAttributesTest extends TestCase
{
    use UsesProductAiDatabase { setUp as protected setupAi; }

    protected CatalogAttributeService $catalog;

    protected int $categoryId;

    protected function setUp(): void
    {
        $this->setupAi();
        Schema::table('categories', fn (Blueprint $t) => $t->string('ref_code')->nullable());
        Schema::table('products', function (Blueprint $t) {
            $t->integer('color_parent_id')->nullable();
            $t->integer('childcategory_id')->nullable();
            $t->string('product_role')->default('P');
            $t->string('color')->nullable();
            $t->string('size')->nullable();
        });
        (require database_path('migrations/2026_04_07_151651_create_languages_table.php'))->up();
        (require database_path('migrations/2026_10_19_120000_create_catalog_product_attributes.php'))->up();
        $this->categoryId = DB::table('categories')->insertGetId(['name' => 'Óptica', 'slug' => 'optico', 'ref_code' => '13']);
        $this->catalog = app(CatalogAttributeService::class);
        config(['catalog-sync.enabled' => false]);
    }

    protected function definition(string $name = 'Material', string $type = 'single', bool $inherit = true): CatalogAttribute
    {
        return $this->catalog->saveDefinition(['data_type' => $type, 'unit' => $type === 'number' ? 'mm' : null, 'is_active' => true, 'inherit_from_parent' => $inherit,
            'translate' => $this->labels($name)]);
    }

    protected function labels(string $name): array
    {
        return ['es' => ['name' => $name], 'pt-br' => ['name' => 'PT '.$name], 'en' => ['name' => 'EN '.$name]];
    }

    protected function product(string $sku, ?int $parent = null): Product
    {
        $id = DB::table('products')->insertGetId(['sku' => $sku, 'category_id' => $this->categoryId, 'parent_id' => $parent, 'price' => 10, 'name' => 'Manual', 'description' => 'Conservar', 'status' => 1]);

        return Product::withoutGlobalScopes()->findOrFail($id);
    }

    protected function assign(Product $p, CatalogAttribute $a, array $row): void
    {
        $this->catalog->saveProduct($p, [['attribute_id' => $a->id, 'mode' => 'value'] + $row], null);
    }

    public function test_dictionary_http_crud_translations_options_and_duplicate_validation(): void
    {
        $editor = User::factory()->create(['user_type' => User::TYPE_ADMIN_EDITOR]);
        $payload = ['data_type' => 'multiple', 'is_active' => true, 'inherit_from_parent' => false, 'translate' => $this->labels('Color de lente')];
        $response = $this->actingAs($editor)->postJson(route('admin.catalog-attributes.store'), $payload)->assertOk();
        $id = $response->json('item.id');
        $this->assertDatabaseHas('languages', ['key' => 'catalog_attr_color_de_lente', 'es' => 'Color de lente', 'en' => 'EN Color de lente']);
        $this->postJson(route('admin.catalog-attributes.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('code');
        $option = $this->postJson(route('admin.catalog-attributes.options.store', $id), ['is_active' => true, 'translate' => $this->labels('Gris')])->assertOk();
        $this->assertNotEmpty($option->json('option_id'));
        $this->putJson(route('admin.catalog-attributes.options.update',[$id,$option->json('option_id')]),['is_active'=>true,'translate'=>$this->labels('Gris actualizado')])->assertOk();
        $this->getJson(route('admin.catalog-attributes.index'))->assertOk()->assertJsonPath('items.0.options.0.code', 'gris');
        $this->putJson(route('admin.catalog-attributes.update', $id), array_replace($payload, ['is_active' => false]))->assertOk();
        $this->assertFalse(CatalogAttribute::find($id)->is_active);
    }

    public function test_parent_inheritance_override_reset_block_and_color_isolation(): void
    {
        $a = $this->definition('Polarizado', 'boolean');
        $parent = $this->product('P');
        $child = $this->product('F', $parent->id);
        $color = $this->product('C');
        DB::table('products')->where('id', $color->id)->update(['color_parent_id' => $parent->id]);
        $this->assign($parent, $a, ['boolean' => true]);
        $this->assertTrue($this->catalog->effective($child)[$a->id]['inherited']);
        $this->assertSame([], $this->catalog->effective($color->fresh()));
        $this->assign($child, $a, ['boolean' => false]);
        $this->assertFalse($this->catalog->effective($child)[$a->id]['boolean']);
        $this->catalog->saveProduct($child, [['attribute_id' => $a->id, 'mode' => 'inherit']], null);
        $this->assertTrue($this->catalog->effective($child)[$a->id]['boolean']);
        $this->catalog->saveProduct($child, [['attribute_id' => $a->id, 'mode' => 'block']], null);
        $this->assertSame('block', $this->catalog->effective($child)[$a->id]['mode']);
        $this->assertSame([], $this->catalog->missing($child));
    }

    public function test_numeric_attributes_do_not_inherit_and_any_dictionary_attribute_can_be_added(): void
    {
        $a = $this->definition('Puente', 'number', false);
        $p = $this->product('P');
        $f = $this->product('F', $p->id);
        $this->assign($p, $a, ['number' => '18.5']);
        $this->assertSame([], $this->catalog->effective($f));
        $this->assign($f, $a, ['number' => '19']);
        $this->assertSame('19.000000', $this->catalog->effective($f)[$a->id]['number']);
    }

    public function test_options_are_normalized_and_foreign_options_are_rejected_atomically(): void
    {
        $a = $this->definition('Forma');
        $b = $this->definition('Material');
        $p = $this->product('P');
        $o = $this->catalog->saveOption($a, ['translate' => $this->labels('Oval'), 'is_active' => true]);
        $this->assign($p, $a, ['options' => [$o->id]]);
        try {
            $this->assign($p, $b, ['options' => [$o->id]]);
            $this->fail('Foreign option accepted');
        } catch (ValidationException) {
        }
        $this->assertDatabaseCount('product_attribute_values', 1);
        $this->assertDatabaseHas('product_attribute_value_options', ['option_id' => $o->id]);
    }

    public function test_manual_translation_keys_and_used_types_are_protected(): void
    {
        $a = $this->definition('Material');
        try {
            $a->language->update(['key' => 'changed']);
            $this->fail('Key changed');
        } catch (ValidationException) {
        }
        try {
            $a->language->delete();
            $this->fail('Translation deleted');
        } catch (ValidationException) {
        }
        $this->assertDatabaseHas('languages', ['key' => 'catalog_attr_material']);
    }

    public function test_ai_only_accepts_exact_sku_supported_values_and_never_replaces_manual_values(): void
    {
        $a = $this->definition('Polarizado', 'boolean', false);
        $p = $this->product('SKU');
        $result = $this->proposal($a, $p);
        $invalid = $result;
        $invalid['proposal']['catalog_attributes'][0]['variant_match'] = false;
        $this->catalog->applyAi($p, $invalid, null);
        $this->assertDatabaseCount('product_attribute_values', 0);
        $invalid = $result;
        $invalid['proposal']['catalog_attributes'][0]['source_url'] = 'https://unvisited.example';
        $this->catalog->applyAi($p, $invalid, null);
        $this->assertDatabaseCount('product_attribute_values', 0);
        $this->catalog->applyAi($p, $result, null);
        $this->assertDatabaseHas('product_attribute_values', ['source' => 'ai', 'boolean_value' => 1]);
        $this->assign($p, $a, ['boolean' => false]);
        $this->catalog->applyAi($p, $result, null);
        $this->assertFalse($this->catalog->effective($p)[$a->id]['boolean']);
        $this->catalog->saveProduct($p, [['attribute_id' => $a->id, 'mode' => 'inherit']], null);
        $this->assertContains($a->id, $this->catalog->aiContext($p)['protected_ids']);
        $this->assertSame(0, $this->catalog->applyAi($p, $result, null));
    }

    protected function proposal(CatalogAttribute $a, Product $p): array
    {
        return ['research' => ['status' => 'matched', 'sources' => [['url' => 'https://manufacturer.example/model']]], 'proposal' => ['catalog_attributes' => [
            ['code' => $a->code, 'option_codes' => [], 'boolean_value' => true, 'number_value' => null, 'confidence' => 'high', 'source_url' => 'https://manufacturer.example/model', 'evidence' => 'Exact SKU confirmed polarized', 'product_sku' => $p->sku, 'variant_match' => true],
        ]]];
    }

    public function test_attributes_only_batch_includes_prepared_active_skus_and_skips_complete_values(): void
    {
        $a = $this->definition('Polarizado', 'boolean', false);
        $p = $this->product('PREPARED');
        ProductAiPreparation::create(['product_id' => $p->id, 'status' => 'completed']);
        $batch = ProductAiBatch::create(['source_type' => 'paste', 'status' => 'draft', 'attributes_only' => true]);
        app(ProductAiBatchDraftBuilder::class)->rebuild($batch, collect([$p->sku]), 'skus');
        $this->assertSame('ready', $batch->items()->first()->status);
        $this->assign($p, $a, ['boolean' => false]);
        app(ProductAiBatchDraftBuilder::class)->rebuild($batch, collect([$p->sku]), 'skus');
        $this->assertSame('skipped', $batch->items()->first()->status);
    }

    public function test_attributes_only_worker_preserves_content_and_preparation(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        $a = $this->definition('Polarizado', 'boolean', false);
        $p = $this->product('PREPARED');
        $subcategoryId = DB::table('subcategories')->insertGetId(['name' => 'Gafas de sol', 'slug' => 'gafas-de-sol', 'category_id' => $this->categoryId]);
        DB::table('products')->where('id', $p->id)->update(['subcategory_id' => $subcategoryId]);
        ProductAiPreparation::create(['product_id' => $p->id, 'status' => 'completed']);
        $batch = ProductAiBatch::create(['source_type' => 'paste', 'status' => 'queued', 'attributes_only' => true]);
        $item = $batch->items()->create(['product_id' => $p->id, 'status' => 'queued', 'target_product_ids' => [$p->id]]);
        $ai = $this->mock(OpenAICatalogService::class);
        $ai->shouldReceive('generateAttributesOnly')->once()->andReturn($this->proposal($a, $p));
        $ai->shouldNotReceive('generateProductProposal');
        (new GenerateProductAiPreparation($item->id, $p->id))->handle($ai, app(ProductAiProposalApplier::class));
        $this->assertDatabaseHas('products', ['id' => $p->id, 'name' => 'Manual', 'description' => 'Conservar', 'status' => 1]);
        $this->assertDatabaseHas('product_ai_preparations', ['product_id' => $p->id, 'status' => 'completed']);
        $this->assertDatabaseHas('product_attribute_values', ['product_id' => $p->id, 'source' => 'ai']);
    }

    public function test_translation_uses_existing_ai_settings_without_web_research(): void
    {
        config(['services.openai.api_key' => 'test-key', 'services.openai.base_url' => 'https://api.openai.com/v1']);
        Http::fake(['api.openai.com/*' => Http::response(['status' => 'completed', 'output' => [['type' => 'message', 'content' => [['type' => 'output_text', 'text' => json_encode(['es' => 'Material', 'pt' => 'Material', 'en' => 'Material'])]]]]])]);
        $this->assertSame('Material', app(OpenAICatalogService::class)->translateCatalogLabel('Material', 'Montura')['en']);
        Http::assertSent(fn ($request) => ! isset($request['tools']) && $request['text']['format']['strict'] === true);
    }

    public function test_replication_maps_translation_attribute_option_and_product_ids_and_reset(): void
    {
        config(['database.connections.attribute_peer' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
        $peer = DB::connection('attribute_peer');
        foreach (DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name IN ('languages','categories','products','catalog_attributes','catalog_attribute_options','product_attribute_values','product_attribute_value_options') ORDER BY name") as $table) {
            $peer->statement($table->sql);
        }
        foreach (DB::select("SELECT sql FROM sqlite_master WHERE type='index' AND sql IS NOT NULL AND tbl_name IN ('languages','catalog_attributes','catalog_attribute_options','product_attribute_values','product_attribute_value_options')") as $index) {
            $peer->statement($index->sql);
        }
        $peer->table('languages')->insert(['id' => 40, 'key' => 'unrelated', 'pt' => 'Outro', 'es' => 'Otro', 'en' => 'Other']);
        $peer->table('catalog_attributes')->insert(['id' => 90, 'code' => 'unrelated', 'language_id' => 40, 'data_type' => 'boolean', 'revision' => 'other']);
        $peer->table('catalog_attribute_options')->insert(['id' => 80, 'attribute_id' => 90, 'code' => 'other', 'language_id' => 40, 'revision' => 'other']);
        $peer->table('categories')->insert(['id' => 777, 'name' => 'Óptica', 'slug' => 'optico', 'ref_code' => '13']);
        $a = $this->definition('Forma');
        $o = $this->catalog->saveOption($a, ['translate' => $this->labels('Oval'), 'is_active' => true]);
        $p = $this->product('SYNC');
        $peer->table('products')->insert(['id' => 888, 'sku' => $p->sku]);
        $this->assign($p, $a, ['options' => [$o->id]]);
        $sync = app(CatalogAttributeSyncService::class);
        $sync->dictionary($peer);
        $sync->product($peer, $p->id);
        $target = $peer->table('catalog_attributes')->where('code', $a->code)->first();
        $this->assertNotSame($a->id, $target->id);
        $this->assertNotSame($a->language_id, $target->language_id);
        $value = $peer->table('product_attribute_values')->where('product_id', 888)->first();
        $this->assertSame($target->id, $value->attribute_id);
        $this->assertNotSame($o->id, $peer->table('product_attribute_value_options')->where('value_id', $value->id)->value('option_id'));
        $this->assertSame('manual', $value->source);
        $this->catalog->saveProduct($p, [['attribute_id' => $a->id, 'mode' => 'inherit']], null);
        $sync->product($peer, $p->id);
        $this->assertSame('inherit', $peer->table('product_attribute_values')->where('product_id', 888)->value('mode'));
        $this->assertSame(0, $peer->table('product_attribute_value_options')->count());
        $peer->table('product_attribute_values')->where('product_id', 888)->update(['mode' => 'block', 'source' => 'manual', 'updated_at' => '2099-01-01 00:00:00.000000']);
        $sync->product($peer, $p->id);
        $this->assertSame('block', $peer->table('product_attribute_values')->where('product_id', 888)->value('mode'));
        // Manual decisions take precedence even when the automatic peer clock is ahead.
        $peer->table('product_attribute_values')->where('product_id', 888)->update(['source' => 'ai']);
        $sync->product($peer, $p->id);
        $this->assertSame('inherit', $peer->table('product_attribute_values')->where('product_id', 888)->value('mode'));
        $this->assertSame('manual', $peer->table('product_attribute_values')->where('product_id', 888)->value('source'));
    }

    public function test_full_preparation_saves_sku_measurements_only_on_the_representative(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->integer('updated_by')->nullable();
            $t->timestamp('admin_edited_at')->nullable();
        });
        require_once database_path('migrations/2025_07_11_192100_create_product_translations_table.php');
        (new \CreateProductTranslationsTable)->up();
        $a = $this->definition('Puente', 'number', false);
        $p = $this->product('ROOT');
        $child = $this->product('CHILD', $p->id);
        $result = $this->proposal($a, $p);
        $result['proposal']['catalog_attributes'][0]['boolean_value'] = null;
        $result['proposal']['catalog_attributes'][0]['number_value'] = '18';
        $result['proposal']['commercial_name'] = ['pt_br' => 'Produto', 'es' => 'Producto', 'en' => 'Product'];
        $result['proposal']['descriptions'] = ['pt_br' => 'Descrição', 'es' => 'Descripción', 'en' => 'Description'];
        app(ProductAiProposalApplier::class)->apply($p, $result, null, [$p->id, $child->id]);
        $this->assertDatabaseCount('product_attribute_values', 1);
        $this->assertSame('18.000000', $this->catalog->effective($p)[$a->id]['number']);
        $this->assertSame([], $this->catalog->effective($child));
    }

    public function test_attributes_only_endpoint_and_normal_product_save_preserve_ai_evidence(): void
    {
        $attribute = $this->definition('Polarizado', 'boolean', false);
        $product = $this->product('EXACT');
        $result = $this->proposal($attribute, $product);
        $row = $result['proposal']['catalog_attributes'][0];
        $row['source_url'] = 'https://manufacturer.example/fourth';
        config(['services.openai.api_key' => 'test-key']);
        Http::fake(['api.openai.com/*' => Http::response([
            'status' => 'completed', 'output' => [
                ['type' => 'message', 'content' => [['type' => 'output_text', 'text' => json_encode(['catalog_attributes' => [$row]])]]],
                ['type' => 'web_search_call', 'action' => ['sources' => [
                    ['url' => 'https://first.example'], ['url' => 'https://second.example'], ['url' => 'https://third.example'], ['url' => $row['source_url']],
                ]]],
            ],
        ])]);
        $editor = User::factory()->create(['user_type' => User::TYPE_ADMIN_EDITOR]);
        $response = $this->actingAs($editor)->postJson(route('admin.products.catalog-attributes.complete', $product), [
            'catalog_context' => ['size' => 'L', 'color' => '#aabbcc'],
        ])->assertOk();
        $response->assertJsonCount(3, 'research.sources')->assertJsonPath('attribute_review.accepted', 1);
        $this->assertArrayNotHasKey('validation_sources', $response->json('research'));
        $this->assertDatabaseCount('product_attribute_values', 0);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => data_get(json_decode($request['input'], true), 'size') === 'L'
            && data_get(json_decode($request['input'], true), 'color') === '#AABBCC');

        Schema::table('products', function (Blueprint $table) {
            foreach (['slug', 'highlights', 'stores', 'shipping_country_of_origin', 'shipping_hs_code'] as $column) $table->text($column)->nullable();
            $table->boolean('shipping_is_dangerous_goods')->default(false);
            $table->integer('updated_by')->nullable();
            $table->timestamp('admin_edited_at')->nullable();
        });
        Schema::create('product_additional_categories', function (Blueprint $table) {
            $table->id(); $table->integer('product_id');
        });
        $feed = \Mockery::mock(\App\Services\ProductFeedRefreshService::class);
        $feed->shouldReceive('markDirty')->zeroOrMoreTimes();
        $this->app->instance(\App\Services\ProductFeedRefreshService::class, $feed);
        $this->putJson(route('admin.products.update', $product), [
            'sku' => $product->sku, 'price' => 10, 'stock' => 5, 'size' => 'L', 'colors_values' => ['#AABBCC'],
            'catalog_values' => [$response->json('attribute_review.items.0.value')],
        ])->assertOk();
        $this->assertDatabaseHas('product_attribute_values', ['product_id' => $product->id, 'source' => 'ai', 'boolean_value' => true]);
        $value = \App\Models\ProductAttributeValue::where('product_id', $product->id)->firstOrFail();
        $this->assertSame($row['source_url'], $value->evidence['url']);
    }

    public function test_editor_accepts_fourth_consulted_source_and_preserves_signed_ai_provenance(): void
    {
        $attribute = $this->definition('Polarizado', 'boolean', false);
        $product = $this->product('EXACT');
        $result = $this->proposal($attribute, $product);
        $result['research']['validation_sources'] = $result['research']['sources'];
        $result['research']['sources'] = [['url' => 'https://visible.example']];
        $response = $this->catalog->prepareForEditor($product, $result);
        $this->assertSame(1, $response['attribute_review']['accepted']);
        $this->assertArrayNotHasKey('validation_sources', $response['research']);
        $row = $response['attribute_review']['items'][0]['value'];
        $this->assertNotEmpty($row['ai_proposal']);
        $this->catalog->saveProduct($product, [$row], null);
        $this->assertDatabaseHas('product_attribute_values', ['product_id' => $product->id, 'source' => 'ai', 'boolean_value' => true]);
        $value = \App\Models\ProductAttributeValue::where('product_id', $product->id)->firstOrFail();
        $this->assertSame('https://manufacturer.example/model', $value->evidence['url']);
        $this->assertSame('Exact SKU confirmed polarized', $value->evidence['text']);
    }

    public function test_editor_proposals_reject_tampering_expiry_variant_changes_and_manual_overwrites(): void
    {
        $attribute = $this->definition('Polarizado', 'boolean', false);
        $product = $this->product('EXACT');
        $row = $this->catalog->prepareForEditor($product, $this->proposal($attribute, $product))['attribute_review']['items'][0]['value'];
        $variants = [array_replace($row, ['boolean' => false]), array_replace($row, ['ai_proposal' => 'forged'])];
        foreach ($variants as $invalid) {
            try {
                $this->catalog->saveProduct($product, [$invalid], null);
                $this->fail('Invalid provenance accepted');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('catalog_values', $e->errors());
            }
        }
        $this->travel(25)->hours();
        try {
            $this->catalog->saveProduct($product, [$row], null);
            $this->fail('Expired proposal accepted');
        } catch (ValidationException) {
            $this->assertDatabaseCount('product_attribute_values', 0);
        }
        $this->travelBack();
        $other = $this->product('OTHER');
        try {
            $this->catalog->saveProduct($other, [$row], null);
            $this->fail('Other SKU accepted');
        } catch (ValidationException) {
            $this->assertDatabaseCount('product_attribute_values', 0);
        }
        DB::table('products')->where('id', $product->id)->update(['size' => 'L']);
        try {
            $this->catalog->saveProduct($product->fresh(), [$row], null);
            $this->fail('Changed variant accepted');
        } catch (ValidationException) {
            $this->assertDatabaseCount('product_attribute_values', 0);
        }
        DB::table('products')->where('id', $product->id)->update(['size' => null]);
        $this->assign($product, $attribute, ['boolean' => false]);
        try {
            $this->catalog->saveProduct($product, [$row], null);
            $this->fail('Manual overwrite accepted');
        } catch (ValidationException) {
            $this->assertDatabaseHas('product_attribute_values', ['source' => 'manual', 'boolean_value' => false]);
        }
    }

    public function test_review_explains_rejections_and_retains_protections(): void
    {
        $attribute = $this->definition('Polarizado', 'boolean', false);
        $product = $this->product('EXACT');
        $result = $this->proposal($attribute, $product);
        $valid = $result['proposal']['catalog_attributes'][0];
        $result['proposal']['catalog_attributes'] = [
            array_replace($valid, ['confidence' => 'medium']),
            array_replace($valid, ['evidence' => '']),
            array_replace($valid, ['variant_match' => false]),
            array_replace($valid, ['source_url' => 'https://unvisited.example']),
            array_replace($valid, ['option_codes' => ['invented']]),
            array_replace($valid, ['boolean_value' => 'invalid']),
            $valid, $valid,
        ];
        $review = $this->catalog->reviewAi($product, $result);
        $this->assertSame(8, $review['suggested']);
        $this->assertSame(1, $review['accepted']);
        $this->assertSame(7, $review['rejected']);
        foreach ($review['items'] as $item) {
            if ($item['status'] === 'rejected') $this->assertNotEmpty($item['reason']);
        }
        $this->assertDatabaseCount('product_attribute_values', 0);
        $this->assertSame(1, $this->catalog->applyAi($product, $result, null));
        $this->assertSame(0, $this->catalog->reviewAi($product, $result)['accepted']);
    }

    public function test_all_categories_are_enabled_and_seed_is_additive(): void
    {
        $category = DB::table('categories')->insertGetId(['name' => 'Ropa', 'slug' => 'ropa', 'ref_code' => '3']);
        $p = $this->product('OTHER');
        $p->category_id = $category;
        $this->assertTrue($this->catalog->enabledFor($p));
        $this->artisan('catalog:seed-optical-attributes')->assertSuccessful();
        $this->assertDatabaseCount('catalog_attributes', 15);
        $this->artisan('catalog:seed-optical-attributes')->assertSuccessful();
        $this->assertDatabaseCount('catalog_attributes', 15);
        $this->assertDatabaseCount('product_attribute_values', 0);
    }
}
