<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductAiBatchItem;
use App\Models\ProductAiPreparation;
use App\Services\OpenAICatalogService;
use App\Services\ProductAiProposalApplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateProductAiPreparation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 0;

    public int $maxExceptions = 3;

    public int $timeout = 150;

    public array $backoff = [30, 90, 180];

    public function __construct(public int $itemId, public int $productId)
    {
        $this->onConnection('product-ai');
        $this->onQueue('product-ai');
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('product-ai-product-'.$this->productId))->releaseAfter(30)->expireAfter(180)];
    }

    public function handle(OpenAICatalogService $openAi, ProductAiProposalApplier $applier): void
    {
        $item = ProductAiBatchItem::with('batch')->find($this->itemId);
        if (! $item || ! in_array($item->status, [ProductAiBatchItem::STATUS_QUEUED, ProductAiBatchItem::STATUS_PROCESSING], true)) {
            return;
        }

        if (app(\App\Services\ProductAiSettingsService::class)->unavailableReason()) {
            $this->release(60);

            return;
        }

        $item->update([
            'status' => ProductAiBatchItem::STATUS_PROCESSING,
            'started_at' => $item->started_at ?: now(),
            'message' => null,
        ]);
        $item->batch?->refreshProgress();

        $product = Product::withoutGlobalScopes()
            ->with(['brand:id,name', 'category:id,name', 'subcategory:id,name', 'categoriasFilhas:id,name'])
            ->find($item->product_id);

        if (! $product) {
            $this->finishAsFailed($item, 'El producto ya no existe.');

            return;
        }

        if ($product->aiPreparation?->status === ProductAiPreparation::STATUS_COMPLETED) {
            $item->update([
                'status' => ProductAiBatchItem::STATUS_COMPLETED,
                'message' => Product::hasUsableImage($product->photo, $product->gallery)
                    ? 'Producto preparado.'
                    : 'Información generada; falta fotografía.',
                'finished_at' => now(),
            ]);
            $item->batch?->refreshProgress();

            return;
        }

        try {
            $result = $openAi->generateProductProposal($product);
            if (data_get($result, 'research.status') !== 'matched') {
                foreach ($this->targetProductIds($item) as $targetProductId) {
                    ProductAiPreparation::updateOrCreate(
                        ['product_id' => $targetProductId],
                        [
                            'status' => ProductAiPreparation::STATUS_NOT_FOUND,
                            'sources' => array_slice((array) data_get($result, 'research.sources', []), 0, 3),
                            'confidence' => data_get($result, 'proposal.confidence'),
                            'model' => data_get($result, 'source.model'),
                            'error_message' => data_get($result, 'research.warning') ?: 'No se encontró una coincidencia web confiable.',
                            'generated_at' => now(),
                            'completed_at' => null,
                        ],
                    );
                }
                $item->update([
                    'status' => ProductAiBatchItem::STATUS_NOT_FOUND,
                    'message' => 'No se encontró una coincidencia web confiable.',
                    'finished_at' => now(),
                ]);
                $item->batch?->refreshProgress();

                return;
            }

            $applier->apply(
                $product,
                $result,
                $item->batch?->created_by,
                (array) $item->target_product_ids,
            );
            $freshProduct = Product::withoutGlobalScopes()->findOrFail($product->id);
            $item->update([
                'status' => ProductAiBatchItem::STATUS_COMPLETED,
                'message' => Product::hasUsableImage($freshProduct->photo, $freshProduct->gallery)
                    ? 'Producto preparado.'
                    : 'Información generada; falta fotografía.',
                'finished_at' => now(),
            ]);
            $item->batch?->refreshProgress();
        } catch (\Illuminate\Validation\ValidationException $exception) {
            // The setting may have changed after the initial worker check.
            $item->update(['status' => ProductAiBatchItem::STATUS_QUEUED]);
            $this->release(60);
        } catch (Throwable $exception) {
            report($exception);
            if ($this->isRetryable($exception)) {
                throw $exception;
            }

            $this->finishAsFailed($item, $this->safeMessage($exception));
        }
    }

    public function failed(?Throwable $exception): void
    {
        $item = ProductAiBatchItem::with('batch')->find($this->itemId);
        if ($item) {
            $this->finishAsFailed($item, $this->safeMessage($exception));
        }
    }

    private function finishAsFailed(ProductAiBatchItem $item, string $message): void
    {
        foreach ($this->targetProductIds($item) as $targetProductId) {
            ProductAiPreparation::updateOrCreate(
                ['product_id' => $targetProductId],
                [
                    'status' => ProductAiPreparation::STATUS_FAILED,
                    'sources' => [],
                    'confidence' => null,
                    'model' => (string) config('services.openai.model'),
                    'error_message' => $message,
                    'generated_at' => now(),
                    'completed_at' => null,
                ],
            );
        }
        $item->update([
            'status' => ProductAiBatchItem::STATUS_FAILED,
            'message' => $message,
            'finished_at' => now(),
        ]);
        $item->batch?->refreshProgress();
    }

    private function isRetryable(Throwable $exception): bool
    {
        for ($current = $exception; $current; $current = $current->getPrevious()) {
            if ($current instanceof RequestException) {
                $status = $current->response?->status();

                return $status === null || $status === 408 || $status === 409 || $status === 429 || $status >= 500;
            }
        }

        return true;
    }

    private function safeMessage(?Throwable $exception): string
    {
        $message = trim((string) $exception?->getMessage());

        return $message !== '' && $exception instanceof \RuntimeException
            ? mb_substr($message, 0, 1000)
            : 'No fue posible generar la propuesta.';
    }

    private function targetProductIds(ProductAiBatchItem $item): array
    {
        return collect($item->target_product_ids ?: [$item->product_id])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
