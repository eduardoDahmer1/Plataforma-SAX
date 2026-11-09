<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\OpenAICatalogService;
use App\Services\ProductAiEditorGeneration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GenerateProductAiEditor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;
    public int $timeout = 150;
    public bool $failOnTimeout = true;

    public function __construct(public string $generationId)
    {
        $this->onConnection('product-ai');
        $this->onQueue('product-ai');
    }

    public function handle(ProductAiEditorGeneration $generation, OpenAICatalogService $ai): void
    {
        $key = $generation->key($this->generationId);
        $state = Cache::get($key);
        if (! $state || $state['status'] !== 'queued' || $state['deadline'] < time()) return;
        $state['status'] = 'processing';
        $state['deadline'] = time() + 170;
        Cache::put($key, $state, now()->addMinutes(30));
        try {
            $product = Product::withoutGlobalScopes()->findOrFail($state['product_id']);
            $product->fill($state['context']);
            $state['result'] = $generation->generate($product, $ai, $state['kind']);
            $state['status'] = 'completed';
            unset($state['context']);
            Cache::put($key, $state, now()->addMinutes(30));
        } catch (Throwable $exception) {
            report($exception);
            $this->failed($exception);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $key = app(ProductAiEditorGeneration::class)->key($this->generationId);
        $state = Cache::get($key);
        if (! $state) return;
        $state['status'] = 'failed';
        $state['message'] = $exception instanceof \RuntimeException
            ? $exception->getMessage() : 'No se pudo completar la generación. El incidente quedó registrado para revisión.';
        unset($state['context']);
        Cache::put($key, $state, now()->addMinutes(30));
    }
}
