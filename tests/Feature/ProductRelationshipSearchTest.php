<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\ProductRelationshipSearch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductRelationshipSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('brand_id')->nullable();
            foreach (['sku', 'name', 'external_name', 'photo', 'color', 'size', 'product_role'] as $field) {
                $table->string($field)->nullable();
            }
            $table->integer('stock')->default(0);
            $table->decimal('price')->default(10);
            $table->unsignedBigInteger('parent_id')->nullable();
        });
        DB::table('brands')->insert(['id' => 1, 'name' => 'FERRA']);
    }

    private function product(string $name, array $attributes = []): Product
    {
        $id = DB::table('products')->insertGetId(array_merge(['external_name' => $name, 'brand_id' => 1, 'product_role' => 'P'], $attributes));

        return Product::with('brand')->findOrFail($id);
    }

    public function test_real_fantasy_format_finds_all_sizes_and_other_compound_colors(): void
    {
        DB::table('brands')->where('id', 1)->update(['name' => 'Salvatore Ferragamo']);
        $current = $this->product('FERRA ZAPATO FANTASY #9M *KARUN NERO');
        $sizeIds = [];
        foreach (['7M', '6M', '7'] as $size) {
            $sizeIds[] = $this->product("FERRA ZAPATO FANTASY #{$size} *KARUN NERO")->id;
        }
        $otherColor = $this->product('FERRA ZAPATO FANTASY #9M *KARUN BIANCO');
        $this->product('FERRA ZAPATO FANTASY #6M *KARUN BIANCO', [
            'parent_id' => $otherColor->id, 'product_role' => 'F',
        ]);
        $this->product('FERRA ZAPATO DIFFERENT #7M *KARUN NERO');
        $controller = app(\App\Http\Controllers\Admin\ProductControllerAdmin::class);
        $this->assertSame('FERRA ZAPATO FANTASY', $current->relationshipSearchTerm());
        foreach (['size', 'color'] as $context) {
            $response = $controller->search(\Illuminate\Http\Request::create('/products/search', 'GET', [
                'q' => $current->relationshipSearchTerm(), 'exclude_id' => $current->id,
                'context' => $context, 'strict_family' => 1,
            ]))->getData(true);
            $this->assertEqualsCanonicalizing($context === 'size' ? $sizeIds : [$otherColor->id], array_column($response, 'id'));
            $this->assertSame(array_fill(0, count($response), true), array_column($response, 'auto_select'));
            foreach ($response as $suggestion) {
                $this->assertSame('FERRA ZAPATO FANTASY', $suggestion['reference']);
                $this->assertNotEmpty($suggestion['size']);
            }
        }
    }

    public function test_both_name_fields_are_parsed_separately_and_entities_do_not_become_model_numbers(): void
    {
        $current = $this->product('FERRA ZAPATO FANTASY #9M&#x20;*KARUN&nbsp;NERO');
        $candidate = $this->product('FERRA ZAPATO FANTASY #7M *KARUN NERO', [
            'name' => 'FERRA ZAPATO FANTASY #7M *KARUN NERO',
        ]);
        $service = app(ProductRelationshipSearch::class);
        $this->assertTrue($service->compatibleSize($current, $candidate));
        $this->assertSame([$candidate->id], $service->search($current->external_name, $current, 'size')->pluck('id')->all());
        $match = $service->match($candidate, 'FANTASY 7M', $current);
        $this->assertNotNull($match); // An explicit size query remains searchable.
        $this->assertFalse($service->compatibleSize($current, $this->product('FERRA ZAPATO OTHER #7M *KARUN NERO')));
    }

    public function test_search_expands_color_names_and_preserves_model_numbers_and_brand(): void
    {
        $current = $this->product('FERRA ZAPATO FANTASY 7 KARUN NERO #40 *001');
        $match = $this->product('KÁRUN FANTASY-7 BIANCO #40 *002');
        $this->product('FERRA ZAPATO FANTASY 8 KARUN BIANCO #40 *002');
        DB::table('brands')->insert(['id' => 2, 'name' => 'OTHER']);
        $this->product('FANTASY 7 KARUN BIANCO #40 *002', ['brand_id' => 2]);
        $results = app(ProductRelationshipSearch::class)->search($current->relationshipSearchTerm(), $current, 'color');
        $this->assertSame([$match->id], $results->pluck('id')->all());
        $this->assertFalse($results->first()->auto_select);
    }

    public function test_all_sizes_are_returned_beyond_previous_limit_and_exact_matches_are_preselected(): void
    {
        $current = $this->product('MODEL AB123 #0 *001');
        for ($i = 1; $i <= 270; $i++) {
            $this->product("MODEL AB123 #{$i} *001");
        }
        $this->product('MODEL AB123 #271 *002');
        $results = app(ProductRelationshipSearch::class)->search('AB123', $current, 'size');
        $this->assertCount(270, $results);
        $this->assertTrue($results->every(fn ($p) => $p->auto_select));
        $this->assertCount(270, $results->unique('id'));
    }

    public function test_brand_search_and_color_grouping_do_not_hide_different_models(): void
    {
        $one = $this->product('MODEL AB123 #40 *001');
        $this->product('MODEL AB123 #41 *001', ['product_role' => 'F', 'parent_id' => $one->id]);
        $two = $this->product('MODEL CD456 #40 *001');
        $results = app(ProductRelationshipSearch::class)->search('ferra', null, 'color');
        $this->assertEqualsCanonicalizing([$one->id, $two->id], $results->pluck('id')->all());
    }

    public function test_controller_returns_all_colors_and_ranks_exact_matches_first(): void
    {
        $current = $this->product('FERRA MODEL AB123 #40 *000');
        for ($i = 1; $i <= 260; $i++) {
            $this->product("FERRA MODEL AB123 #40 *{$i}");
        }
        $this->product('FERRA MODEL AB123 #41 *1', ['product_role' => 'F']);
        $response = app(\App\Http\Controllers\Admin\ProductControllerAdmin::class)->search(
            \Illuminate\Http\Request::create('/products/search', 'GET', [
                'q' => 'AB123', 'context' => 'color', 'exclude_id' => $current->id, 'strict_family' => 1,
            ])
        );
        $this->assertCount(260, $response->getData(true));
        $this->assertTrue($response->getData(true)[0]['auto_select']);
    }

    public function test_partial_matches_require_review_and_can_be_selected_as_sizes(): void
    {
        $current = $this->product('FERRA FANTASY KARUN SPECIAL #40 *001');
        $candidate = $this->product('FERRA FANTASY KARUN #41 *001');
        $service = app(ProductRelationshipSearch::class);
        $results = $service->search($current->relationshipSearchTerm(), $current, 'size');
        $this->assertCount(1, $results);
        $this->assertFalse($results->first()->auto_select);
        $this->assertTrue($service->compatibleSize($current, $candidate));
    }
}
