<?php

namespace Tests\Feature;

use App\Jobs\GenerateProductAiEditor;
use App\Models\Product;
use App\Models\User;
use App\Services\OpenAICatalogService;
use App\Services\ProductAiEditorGeneration;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\Support\UsesProductAiDatabase;
use Tests\TestCase;

class ProductAiAsyncGenerationTest extends TestCase
{
    use UsesProductAiDatabase;

    public function test_start_returns_before_generation_and_duplicate_requests_reuse_one_job(): void
    {
        config(['services.openai.api_key'=>'test-key', 'catalog-sync.enabled'=>false]);
        Bus::fake();
        $user = User::withoutEvents(fn () => User::factory()->create(['user_type'=>User::TYPE_ADMIN_EDITOR]));
        $product = Product::create(['sku'=>'ASYNC', 'name'=>'Original', 'price'=>10]);
        $payload = ['async'=>true, 'request_id'=>(string) Str::uuid(), 'catalog_context'=>['name'=>'En el editor']];
        $first = $this->actingAs($user)->postJson(route('admin.products.completeWithAi', $product), $payload)->assertStatus(202);
        $second = $this->postJson(route('admin.products.completeWithAi', $product), $payload)->assertStatus(202);
        $this->assertSame($first->json('generation_id'), $second->json('generation_id'));
        Bus::assertDispatchedTimes(GenerateProductAiEditor::class, 1);
        Bus::assertDispatched(GenerateProductAiEditor::class, fn ($job) => $job->connection === 'product-ai' && $job->queue === 'product-ai');
        $this->getJson($first->json('status_url'))->assertOk()->assertJsonPath('status', 'queued')->assertJsonMissingPath('context');
        $this->assertSame('Original', $product->fresh()->name);
        $other = User::withoutEvents(fn () => User::factory()->create(['user_type'=>User::TYPE_ADMIN_EDITOR]));
        $this->actingAs($other)->getJson($first->json('status_url'))->assertNotFound();
    }

    public function test_worker_uses_unsaved_editor_context_and_returns_result_without_saving_product(): void
    {
        config(['catalog-sync.enabled'=>false]);
        Bus::fake();
        $product = Product::create(['sku'=>'ASYNC', 'name'=>'Original', 'price'=>10]);
        $product->name = 'En el editor';
        $generation = app(ProductAiEditorGeneration::class);
        $receipt = $generation->start($product, 'catalog', 1, (string) Str::uuid());
        $ai = \Mockery::mock(OpenAICatalogService::class);
        $ai->shouldReceive('generateProductProposal')->once()->withArgs(fn ($p) => $p->name === 'En el editor')->andReturn([
            'proposal'=>['catalog_attributes'=>[], 'confidence'=>'high'], 'research'=>['status'=>'matched', 'sources'=>[]], 'source'=>['model'=>'test'],
        ]);
        (new GenerateProductAiEditor($receipt['generation_id']))->handle($generation, $ai);
        $result = $generation->status($product, $receipt['generation_id'], 1);
        $this->assertSame('completed', $result['status']);
        $this->assertSame('generated', $result['result']['preparation']['status']);
        $this->assertSame('Original', $product->fresh()->name);
    }

    public function test_worker_failure_is_visible_and_expired_work_cannot_start(): void
    {
        Bus::fake();
        $product = Product::create(['sku'=>'ASYNC', 'name'=>'Original', 'price'=>10]);
        $generation = app(ProductAiEditorGeneration::class);
        $receipt = $generation->start($product, 'catalog', 1, (string) Str::uuid());
        $job = new GenerateProductAiEditor($receipt['generation_id']);
        $job->failed(new \RuntimeException('Prueba de fallo'));
        $this->assertSame('Prueba de fallo', $generation->status($product, $receipt['generation_id'], 1)['message']);
        $state = Cache::get($generation->key($receipt['generation_id']));
        $state['status'] = 'queued'; $state['deadline'] = time() - 1;
        Cache::put($generation->key($receipt['generation_id']), $state);
        $ai = \Mockery::mock(OpenAICatalogService::class);
        $ai->shouldNotReceive('generateProductProposal');
        $job->handle($generation, $ai);
        $this->assertSame('failed', $generation->status($product, $receipt['generation_id'], 1)['status']);
    }
}
