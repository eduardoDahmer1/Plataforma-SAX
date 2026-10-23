<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ProductControllerAdmin;
use App\Models\Product;
use App\Models\ProductAiPreparation;
use App\Services\OpenAICatalogService;
use Illuminate\Support\Facades\DB;
use Tests\Support\UsesProductAiDatabase;
use Tests\TestCase;

class ProductAiCompletionTest extends TestCase
{
    use UsesProductAiDatabase;

    public function test_completing_each_product_family_returns_content_without_saving_the_product(): void
    {
        config(['services.openai.api_key' => 'test-key', 'catalog-sync.enabled' => false]);
        foreach (['optica' => 149, 'ropa' => 140, 'calzado' => 140, 'perfumeria' => 144] as $family => $category) {
            $id = DB::table('products')->insertGetId([
                'sku' => $family, 'category_id' => $category,
                'name' => 'Nombre original', 'description' => 'Descripción original',
            ]);
            $product = Product::withoutGlobalScopes()->findOrFail($id);
            $before = $product->getRawOriginal();
            $product->setRelation('brand', null)->setRelation('category', null)
                ->setRelation('subcategory', null)->setRelation('categoriasFilhas', null);
            $proposal = [
                'commercial_name' => ['pt_br' => 'MARCA MODELO', 'es' => 'MARCA MODELO', 'en' => 'MARCA MODELO'],
                'descriptions' => ['pt_br' => 'Descrição', 'es' => 'Descripción', 'en' => 'Description'],
                'confidence' => 'high', 'catalog_attributes' => [],
            ];
            $service = \Mockery::mock(OpenAICatalogService::class);
            $service->shouldReceive('generateProductProposal')->once()->with($product)->andReturn([
                'proposal' => $proposal, 'research' => ['status' => 'matched', 'sources' => []],
                'source' => ['model' => 'test'],
            ]);
            $response = app(ProductControllerAdmin::class)->completeWithAi($product, $service);
            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame($proposal, $response->getData(true)['proposal']);
            $this->assertSame($before, $product->fresh()->getRawOriginal(), $family.' must remain unsaved');
            $this->assertSame(ProductAiPreparation::STATUS_GENERATED, ProductAiPreparation::where('product_id', $id)->value('status'));
        }
    }
}
