<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAiBatch;

class ProductCatalogReadiness
{
    public function product(?Product $product): array
    {
        $criteria = [
            'identity' => [
                'label' => 'Nombre e SKU',
                'ready' => $product && filled($product->sku) && filled($product->name ?: $product->external_name),
            ],
            'description' => [
                'label' => 'Descripción',
                'ready' => $product && $this->hasDescription($product->description),
            ],
            'image' => [
                'label' => 'Fotografía',
                'ready' => $product && Product::hasUsableImage($product->photo, $product->gallery),
            ],
            'brand' => [
                'label' => 'Marca',
                'ready' => $product && filled($product->brand_id),
            ],
            'taxonomy' => [
                'label' => 'Categoría, subcategoría y categoría hija',
                'ready' => $product
                    && filled($product->category_id)
                    && filled($product->subcategory_id)
                    && filled($product->childcategory_id),
            ],
            'commercial' => [
                'label' => 'Precio y stock',
                'ready' => $product
                    && is_numeric($product->price)
                    && (float) $product->price > 0
                    && $product->stock !== null,
            ],
        ];

        $ready = collect($criteria)->where('ready', true)->count();
        $percentage = (int) round(($ready / count($criteria)) * 100);

        return [
            'percentage' => $percentage,
            'complete' => $percentage === 100,
            'ready_checks' => $ready,
            'total_checks' => count($criteria),
            'missing' => collect($criteria)
                ->reject(fn (array $criterion) => $criterion['ready'])
                ->pluck('label')
                ->values()
                ->all(),
        ];
    }

    public function batch(ProductAiBatch $batch): array
    {
        $products = $batch->relationLoaded('items') ? $batch->items : $batch->items()->with('product')->get();
        $evaluations = $products->map(fn ($item) => $this->product($item->product));
        $total = $evaluations->count();
        $percentage = $total > 0
            ? (int) round($evaluations->avg('percentage'))
            : 0;

        return [
            'percentage' => $percentage,
            'complete' => $percentage === 100 && $total > 0,
            'complete_products' => $evaluations->where('complete', true)->count(),
            'total_products' => $total,
        ];
    }

    private function hasDescription(?string $description): bool
    {
        $plainText = html_entity_decode(strip_tags(str_ireplace(
            ['<br>', '<br/>', '<br />', '&nbsp;'],
            ' ',
            (string) $description,
        )));

        return trim($plainText) !== '';
    }
}
