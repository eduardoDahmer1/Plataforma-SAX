<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductAiSizeGrouper
{
    /**
     * Groups only explicit size variants. Different brand, reference or color
     * always produce independent groups.
     */
    public function group(Collection $products): array
    {
        $selectedParentIds = $products->pluck('parent_id')->filter()->map(fn ($id) => (int) $id)->flip();
        $groups = [];

        foreach ($products as $product) {
            $groupKey = $this->groupKey($product, $selectedParentIds);
            $groups[$groupKey] ??= [
                'representative_id' => $product->parent_id ? (int) $product->parent_id : (int) $product->id,
                'linked_family' => (bool) $product->parent_id || $selectedParentIds->has((int) $product->id),
                'product_ids' => [],
                'source_skus' => [],
            ];
            $groups[$groupKey]['product_ids'][] = (int) $product->id;
            $groups[$groupKey]['source_skus'][] = (string) $product->sku;

            if (! $groups[$groupKey]['linked_family']) {
                $groups[$groupKey]['representative_id'] = min(
                    $groups[$groupKey]['representative_id'],
                    (int) $product->id,
                );
            }
        }

        return array_values($groups);
    }

    private function groupKey(Product $product, Collection $selectedParentIds): string
    {
        if ($product->parent_id) {
            return 'parent:'.(int) $product->parent_id;
        }
        if ($selectedParentIds->has((int) $product->id)) {
            return 'parent:'.(int) $product->id;
        }

        $size = $product->inferredSize();
        $reference = $product->relationshipReferenceKey();
        if (filled($size) && $reference !== '') {
            return implode('|', [
                'external',
                (int) $product->brand_id,
                $reference,
                $product->relationshipColorKey(),
            ]);
        }

        return 'product:'.(int) $product->id;
    }
}
