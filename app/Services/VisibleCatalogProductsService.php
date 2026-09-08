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
        $parentsWithOwnStock = Product::query()
            ->select('products.id')
            ->where('products.product_role', 'P')
            ->where('products.status', 1)
            ->where('products.stock', '>', 0)
            ->toBase();

        $parentsWithAvailableChild = Product::query()
            ->select('products.parent_id')
            ->where('products.product_role', 'F')
            ->whereNotNull('products.parent_id')
            ->where('products.status', 1)
            ->where('products.stock', '>', 0)
            ->toBase();

        // toBase() applies the Product global scopes before composing the
        // UNION. The outer query stays Eloquent, so it remains suitable for
        // eager loading, ordering and pagination.
        $visibleParentIds = $parentsWithOwnStock->union($parentsWithAvailableChild);

        return Product::query()
            ->where('products.product_role', 'P')
            ->where('products.is_outlet', false)
            ->inActiveCategory()
            ->whereNotNull('products.photo')
            ->where('products.photo', '!=', '')
            ->whereIn('products.id', $visibleParentIds);
    }
}
