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
