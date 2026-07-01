<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function applyActiveProductScope($query)
    {
        return $query
            ->where('status', 1)
            ->where('product_role', 'P')
            ->where('stock', '>', 0)
            ->whereNotNull('photo')
            ->where('photo', '!=', '');
    }

    protected function buildFilterCategoriesTree()
    {
        $categories = \App\Models\Category::where('status', 1)
            ->withCount([
                'products as active_products_count' => fn($q) => $this->applyActiveProductScope($q),
            ])
            ->with([
                'subcategories' => function ($subQuery) {
                    $subQuery
                        ->withCount([
                            'products as active_products_count' => fn($q) => $this->applyActiveProductScope($q),
                        ])
                        ->with([
                            'categoriasfilhas' => function ($childQuery) {
                                $childQuery
                                    ->withCount([
                                        'products as active_products_count' => fn($q) => $this->applyActiveProductScope($q),
                                    ])
                                    ->orderBy('name');
                            },
                        ])
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();

        return $categories
            ->map(function ($category) {
                $filteredSubs = $category->subcategories
                    ->map(function ($subcategory) {
                        $filteredChildren = $subcategory->categoriasfilhas
                            ->filter(fn($child) => (int) $child->active_products_count > 0)
                            ->values();

                        $subcategory->setRelation('categoriasfilhas', $filteredChildren);

                        return $subcategory;
                    })
                    ->filter(function ($subcategory) {
                        return (int) $subcategory->active_products_count > 0
                            || $subcategory->categoriasfilhas->isNotEmpty();
                    })
                    ->values();

                $category->setRelation('subcategories', $filteredSubs);

                return $category;
            })
            ->filter(function ($category) {
                return (int) $category->active_products_count > 0
                    || $category->subcategories->isNotEmpty();
            })
            ->values();
    }

    protected function buildFilterBrandsList()
    {
        return \App\Models\Brand::where('status', 1)
            ->withCount([
                'products as active_products_count' => fn($q) => $this->applyActiveProductScope($q),
            ])
            ->having('active_products_count', '>', 0)
            ->orderBy('name')
            ->get();
    }
}
