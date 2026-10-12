<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds the storefront product-card query around the stable family anchor.
 *
 * Filters may be appended to the returned Eloquent builder (brand_id,
 * category_id, subcategory_id, childcategory_id, search, etc.).
 */
class VisibleCatalogProductsService
{
    public static function builder(): Builder
    {
        return Product::query()
            ->where('products.product_role', 'P')
            ->where('products.is_outlet', false)
            ->inActiveCategory()
            ->whereNotNull('products.photo')
            ->where('products.photo', '!=', '')
            ->where(function (Builder $availability): void {
                $availability
                    ->where(function (Builder $ownStock): void {
                        $ownStock->where('products.status', 1)
                            ->where('products.stock', '>', 0);
                    })
                    ->orWhereExists(function ($availableChild): void {
                        // A consulta anterior materializava um UNION global de
                        // toda a tabela em cada contagem/filtro do catálogo.
                        // O EXISTS usa o índice (parent_id, status, stock) e
                        // para assim que encontra um filho vendável.
                        $availableChild->selectRaw('1')
                            ->from('products as available_children')
                            ->whereColumn('available_children.parent_id', 'products.id')
                            ->where('available_children.product_role', 'F')
                            ->where('available_children.status', 1)
                            ->where('available_children.stock', '>', 0)
                            ->where('available_children.price', '>=', Product::MINIMUM_VISIBLE_PRICE);
                    });
            });
    }
}
