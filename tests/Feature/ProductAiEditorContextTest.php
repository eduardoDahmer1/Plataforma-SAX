<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use App\Services\OpenAICatalogService;
use App\Services\ProductAiEditorContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\UsesProductAiDatabase;
use Tests\TestCase;

class ProductAiEditorContextTest extends TestCase
{
    use UsesProductAiDatabase;

    public function test_batch_context_uses_the_same_portuguese_fallbacks_as_the_editor(): void
    {
        require_once database_path('migrations/2025_07_11_192100_create_product_translations_table.php');
        (new \CreateProductTranslationsTable)->up();
        $product = Product::create(['sku' => 'BATCH-CONTEXT', 'external_name' => 'Nombre original', 'price' => 10]);
        DB::table('product_translations')->insert(['product_id' => $product->id, 'locale' => 'pt-br', 'name' => 'Nome traduzido', 'details' => 'Descrição traduzida']);

        $context = app(ProductAiEditorContext::class)->fromStoredProduct($product);
        $this->assertSame('Nome traduzido', $context->name);
        $this->assertSame('Descrição traduzida', $context->description);
        $this->assertNull($product->fresh()->name);

        DB::table('product_translations')->delete();
        $this->assertSame('Nombre original', app(ProductAiEditorContext::class)->fromStoredProduct($product)->name);
    }

    public function test_current_values_override_loaded_relations_without_saving_the_product(): void
    {
        $old = Brand::forceCreate(['name' => 'Old']);
        $new = Brand::forceCreate(['name' => 'New']);
        $id = DB::table('products')->insertGetId(['sku'=>'CTX', 'price'=>10, 'name'=>'Original', 'brand_id'=>$old->id]);
        $product = Product::findOrFail($id)->setRelation('brand', $old);
        $before = $product->getAttributes();
        $context = app(ProductAiEditorContext::class)->fromRequest($product, Request::create('/', 'POST', [
            'catalog_context'=>['brand_id'=>$new->id,'name'=>'Sin guardar','description'=>'Descripción actual','size'=>'L','color'=>'#aabbcc'],
        ]));
        $this->assertSame('New', $context->brand->name);
        $this->assertSame('Sin guardar', $context->name);
        $this->assertSame('Descripción actual', $context->description);
        $this->assertSame('L', $context->size);
        $this->assertSame('#AABBCC', $context->color);
        $this->assertSame($before, $product->getAttributes());
        $this->assertSame($before, $product->fresh()->getAttributes());
    }

    public function test_context_cannot_change_the_product_identity_or_inject_fields(): void
    {
        $this->expectException(ValidationException::class);
        app(ProductAiEditorContext::class)->fromRequest(new Product(), Request::create('/', 'POST', [
            'catalog_context'=>['sku'=>'FORGED','price'=>1],
        ]));
    }

    public function test_generation_endpoint_receives_current_values_without_saving(): void
    {
        $id = DB::table('products')->insertGetId(['sku'=>'CTX', 'price'=>10, 'name'=>'Original', 'description'=>'Original']);
        $product = Product::findOrFail($id);
        $before = $product->getAttributes();
        $editor = User::factory()->create(['user_type'=>User::TYPE_ADMIN_EDITOR]);
        $ai = \Mockery::mock(OpenAICatalogService::class);
        $ai->shouldReceive('generateProductProposal')->once()->with(\Mockery::on(fn (Product $p) => $p->name === 'Sin guardar' && $p->description === 'Actual'))
            ->andReturn(['proposal'=>['catalog_attributes'=>[]], 'research'=>['status'=>'matched','sources'=>[]], 'source'=>['model'=>'test']]);
        $this->app->instance(OpenAICatalogService::class,$ai);
        $this->actingAs($editor)->postJson(route('admin.products.completeWithAi',$product),[
            'catalog_context'=>['name'=>'Sin guardar','description'=>'Actual'],
        ])->assertOk();
        $this->assertSame($before,$product->fresh()->getAttributes());
    }
}
