<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAiBatch;
use App\Models\ProductAiBatchItem;
use App\Models\ProductAiPreparation;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductAiBatchDraftBuilder
{
    public function __construct(private ProductAiSizeGrouper $grouper)
    {
    }

    public function rebuild(
        ProductAiBatch $batch,
        Collection $rawSkus,
        string $inputField,
        ?int $duplicateCount = null,
    ): void
    {
        $rawSkus = $rawSkus
            ->map(fn ($sku) => trim((string) $sku))
            ->filter()
            ->values();
        $uniqueSkus = $rawSkus->unique(fn ($sku) => mb_strtolower($sku))->values();

        if ($uniqueSkus->isEmpty()) {
            throw ValidationException::withMessages([$inputField => 'No encontramos códigos para procesar.']);
        }
        if ($uniqueSkus->count() > 1000) {
            throw ValidationException::withMessages([$inputField => 'El lote admite como máximo 1.000 códigos únicos.']);
        }

        $batch->items()->delete();

        $products = Product::withoutGlobalScopes()
            ->whereIn('sku', $uniqueSkus)
            ->get(['id', 'sku', 'parent_id', 'status', 'brand_id', 'external_name', 'name', 'color', 'size']);
        $foundBySku = $products->keyBy(fn ($product) => mb_strtolower((string) $product->sku));
        $foundProducts = collect();

        foreach ($uniqueSkus as $sku) {
            $product = $foundBySku->get(mb_strtolower($sku));
            if (! $product) {
                $batch->items()->create([
                    'submitted_sku' => $sku,
                    'source_skus' => [$sku],
                    'status' => ProductAiBatchItem::STATUS_MISSING,
                    'message' => 'SKU no encontrado en la base de datos.',
                ]);

                continue;
            }
            $foundProducts->push($product);
        }

        $groups = $this->grouper->group($foundProducts);
        $candidateRepresentativeIds = collect($groups)->pluck('representative_id')->map(fn ($id) => (int) $id);
        $existingParentIds = Product::withoutGlobalScopes()
            ->whereIn('parent_id', $candidateRepresentativeIds)
            ->pluck('parent_id')
            ->map(fn ($id) => (int) $id)
            ->flip();
        foreach ($groups as &$group) {
            if ($existingParentIds->has((int) $group['representative_id'])) {
                $group['linked_family'] = true;
            }
        }
        unset($group);

        $linkedRootIds = collect($groups)
            ->where('linked_family', true)
            ->pluck('representative_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $linkedProducts = $linkedRootIds->isEmpty()
            ? collect()
            : Product::withoutGlobalScopes()
                ->where(function ($query) use ($linkedRootIds) {
                    $query->whereIn('id', $linkedRootIds)->orWhereIn('parent_id', $linkedRootIds);
                })
                ->get(['id', 'parent_id']);

        foreach ($groups as &$group) {
            if ($group['linked_family']) {
                $rootId = (int) $group['representative_id'];
                $group['target_product_ids'] = $linkedProducts
                    ->filter(fn ($product) => (int) ($product->parent_id ?: $product->id) === $rootId)
                    ->pluck('id')
                    ->merge($group['product_ids'])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();
            } else {
                $group['target_product_ids'] = array_values(array_unique($group['product_ids']));
            }
        }
        unset($group);

        $allTargetIds = collect($groups)->flatMap(fn ($group) => $group['target_product_ids'])->unique()->values();
        $targets = Product::withoutGlobalScopes()
            ->whereIn('id', $allTargetIds)
            ->get(['id', 'sku', 'status'])
            ->keyBy('id');
        $preparations = ProductAiPreparation::whereIn('product_id', $allTargetIds)->get()->keyBy('product_id');
        $eligibleCount = 0;

        foreach ($groups as $group) {
            $representativeId = (int) $group['representative_id'];
            $representative = $targets->get($representativeId);
            $sourceSkus = array_values($group['source_skus']);
            $targetIds = array_values($group['target_product_ids']);
            if (! $representative || $targetIds === []) {
                $batch->items()->create([
                    'submitted_sku' => $sourceSkus[0] ?? null,
                    'source_skus' => $sourceSkus,
                    'target_product_ids' => $targetIds,
                    'status' => ProductAiBatchItem::STATUS_MISSING,
                    'message' => 'El producto representante ya no existe.',
                ]);

                continue;
            }

            $skipMessage = null;
            if (collect($targetIds)->contains(fn ($id) => (bool) $targets->get($id)?->status)) {
                $skipMessage = 'Producto o variante activa; no se modifica contenido publicado.';
            } elseif (collect($targetIds)->contains(fn ($id) => in_array(
                $preparations->get($id)?->status,
                [ProductAiPreparation::STATUS_GENERATED, ProductAiPreparation::STATUS_COMPLETED],
                true,
            ))) {
                $skipMessage = 'La IA ya generó información para este producto.';
            }

            $batch->items()->create([
                'product_id' => $representativeId,
                'submitted_sku' => $representative->sku,
                'source_skus' => $sourceSkus,
                'target_product_ids' => $targetIds,
                'status' => $skipMessage ? ProductAiBatchItem::STATUS_SKIPPED : ProductAiBatchItem::STATUS_READY,
                'message' => $skipMessage ?: (count($sourceSkus) > 1
                    ? count($sourceSkus).' tallas agrupadas en una sola generación.'
                    : null),
            ]);
            if (! $skipMessage) {
                $eligibleCount++;
            }
        }

        if ($eligibleCount > 100) {
            throw ValidationException::withMessages([
                $inputField => "Los códigos forman {$eligibleCount} grupos diferentes que requieren IA. El máximo es 100 por lote.",
            ]);
        }

        $batch->update([
            'input_count' => $uniqueSkus->count(),
            'duplicate_count' => $duplicateCount ?? max(0, $rawSkus->count() - $uniqueSkus->count()),
            'eligible_count' => $eligibleCount,
        ]);
    }
}
