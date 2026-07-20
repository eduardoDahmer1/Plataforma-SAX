<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Services\CategoryDisplayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $displayService;

    public function __construct()
    {
        $this->displayService = app(CategoryDisplayService::class);

        if (request()->is('*edition-privee*')) {
            $customCss = "
                <style>
                    h1, .breadcrumb, .title, h2, span, a { 
                        text-transform: none !important; 
                    }
                    .product-grid-title, .catalog-title {
                        text-transform: lowercase !important;
                    }
                </style>
            ";
            View::share('custom_category_css', $customCss);
        } else {
            View::share('custom_category_css', '');
        }
    }

    protected function applyActiveProductScope($query)
    {
        return $query
            ->where('status', 1)
            ->where('product_role', 'P')
            ->where('stock', '>', 0)
            ->whereNotNull('photo')
            ->where('photo', '!=', '');
    }

    protected function applyCatalogSorting($query, ?string $sortBy)
    {
        return match ($sortBy) {
            'latest' => $query->orderBy('created_at', 'desc'),
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'name_az' => $query->orderBy('external_name', 'asc'),
            default => $query->orderBy('id', 'desc'),
        };
    }

    protected function catalogPerPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 36);

        return in_array($perPage, [36, 72, 100], true) ? $perPage : 36;
    }

    protected function catalogSortBy(Request $request): string
    {
        $sortBy = (string) $request->input('sort_by', '');

        return in_array($sortBy, ['latest', 'price_low', 'price_high', 'name_az'], true)
            ? $sortBy
            : '';
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
                $this->displayService->format($category);

                $filteredSubs = $category->subcategories
                    ->map(function ($subcategory) {
                        $this->displayService->format($subcategory);

                        $filteredChildren = $subcategory->categoriasfilhas
                            ->map(function ($child) {
                                return $this->displayService->format($child);
                            })
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
