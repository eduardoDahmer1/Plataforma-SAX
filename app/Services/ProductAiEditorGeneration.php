<?php

namespace App\Services;

use App\Jobs\GenerateProductAiEditor;
use App\Models\Product;
use App\Models\ProductAiPreparation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductAiEditorGeneration
{
    public function key(string $id): string
    {
        return 'product-ai-editor:'.$id;
    }

    public function start(Product $product, string $kind, int $userId, string $requestId): array
    {
        $context = $product->getDirty();
        ksort($context);
        $fingerprint = hash('sha256', json_encode([$userId, $product->id, $kind, $context, $product->updated_at]));
        return Cache::lock('product-ai-editor-start:'.$fingerprint, 10)->block(3, function () use ($product, $kind, $userId, $requestId, $context, $fingerprint) {
            $requestKey = 'product-ai-editor-request:'.$userId.':'.$requestId;
            $id = Cache::get($requestKey);
            if ($id && ($state = Cache::get($this->key($id))) && $state['fingerprint'] === $fingerprint) return $this->receipt($id, $product);
            $activeKey = 'product-ai-editor-active:'.$fingerprint;
            $id = Cache::get($activeKey);
            if ($id && ($state = Cache::get($this->key($id))) && in_array($state['status'], ['queued', 'processing'], true) && $state['deadline'] > time()) {
                Cache::put($requestKey, $id, now()->addMinutes(30));
                return $this->receipt($id, $product);
            }
            $id = (string) Str::uuid();
            Cache::put($this->key($id), ['status'=>'queued', 'product_id'=>$product->id, 'user_id'=>$userId,
                'kind'=>$kind, 'context'=>$context, 'fingerprint'=>$fingerprint, 'deadline'=>time() + 600], now()->addMinutes(30));
            try {
                GenerateProductAiEditor::dispatch($id);
            } catch (\Throwable $exception) {
                Cache::forget($this->key($id));
                throw $exception;
            }
            Cache::put($requestKey, $id, now()->addMinutes(30));
            Cache::put($activeKey, $id, now()->addMinutes(10));
            return $this->receipt($id, $product);
        });
    }

    private function receipt(string $id, Product $product): array
    {
        return ['status'=>'queued', 'status_url'=>route('admin.products.ai-generation.status', [$product->id, $id]), 'generation_id'=>$id];
    }

    public function status(Product $product, string $id, int $userId): array
    {
        $state = Cache::get($this->key($id));
        abort_unless($state && (int) $state['product_id'] === (int) $product->id && $state['user_id'] === $userId, 404);
        if (in_array($state['status'], ['queued', 'processing'], true) && $state['deadline'] < time()) {
            return ['status'=>'failed', 'message'=>'El proceso de generación no respondió a tiempo. Revisá el worker de la cola product-ai.'];
        }
        return array_intersect_key($state, array_flip(['status', 'result', 'message']));
    }

    public function generate(Product $product, OpenAICatalogService $ai, string $kind): array
    {
        $product->loadMissing(['brand:id,name', 'category:id,name', 'subcategory:id,name', 'categoriasFilhas:id,name']);
        if ($kind === 'description') return $ai->generateDescriptionOnly($product);
        if ($kind === 'attributes') return app(CatalogAttributeService::class)->prepareForEditor($product, $ai->generateAttributesOnly($product));

        $result = $ai->generateProductProposal($product);
        $matched = data_get($result, 'research.status') === 'matched';
        $generatedAt = now()->setMicrosecond(0);
        $result = app(CatalogAttributeService::class)->prepareForEditor($product, $result);
        $preparation = ProductAiPreparation::updateOrCreate(['product_id'=>$product->id], [
            'status'=>$matched ? ProductAiPreparation::STATUS_GENERATED : ProductAiPreparation::STATUS_NOT_FOUND,
            'sources'=>array_slice((array) data_get($result, 'research.sources', []), 0, 3),
            'confidence'=>data_get($result, 'proposal.confidence'), 'model'=>data_get($result, 'source.model'),
            'error_message'=>$matched ? null : data_get($result, 'research.warning'), 'generated_at'=>$generatedAt, 'completed_at'=>null,
        ]);
        $result['preparation'] = ['status'=>$preparation->status, 'generated_at'=>$matched ? $generatedAt->toISOString() : null];
        return $result;
    }
}
