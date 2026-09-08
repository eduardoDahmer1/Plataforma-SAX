<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\SlugRedirect;
use App\Services\VisibleCatalogProductsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function publicIndex(Request $request)
    {
        $page = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "brands_index_visible_catalog_v2_{$page}_" . md5($search);

        $brands = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $visibleBrandIds = VisibleCatalogProductsService::builder()->select('products.brand_id');
            $visibleProductsCount = VisibleCatalogProductsService::builder()
                ->selectRaw('count(*)')
                ->whereColumn('products.brand_id', 'brands.id');

            $query = Brand::where('status', 1)
                ->whereIn('id', $visibleBrandIds)
                ->select('brands.*')
                ->selectSub($visibleProductsCount, 'active_products_count')
                ->orderBy('name');

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->paginate(20)->withQueryString();
        });

        return view('brands.index', compact('brands'));
    }

    public function publicShow($slug, Request $request)
    {
        $page = $request->get('page', 1);
        $sortBy = $this->catalogSortBy($request);
        $perPage = $this->catalogPerPage($request);
        $cacheKey = "brand_show_visible_catalog_v2_{$slug}_page_{$page}_{$sortBy}_{$perPage}";

        try {
            $brand = Cache::remember("brand_visible_catalog_v2_{$slug}", now()->addMinutes(30), function () use ($slug) {
                $visibleBrandIds = VisibleCatalogProductsService::builder()->select('products.brand_id');

                return Brand::where('slug', $slug)
                    ->where('status', 1)
                    ->whereIn('id', $visibleBrandIds)
                    ->firstOrFail();
            });
        } catch (ModelNotFoundException $e) {
            if ($redirectUrl = SlugRedirect::resolveUrl('brand', $slug)) {
                return redirect($redirectUrl, 301);
            }
            throw $e;
        }

        $products = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($brand, $sortBy, $perPage) {
            $productsQuery = VisibleCatalogProductsService::builder()
                ->where('products.brand_id', $brand->id)
                ->with(['brand', 'category', 'translations']);
            $this->applyCatalogSorting($productsQuery, $sortBy);

            return $productsQuery
                ->paginate($perPage)
                ->withQueryString();
        });

        $categoriesTree = Cache::remember('filter_full_tree_visible_catalog_v2', now()->addHours(1), fn() => $this->buildFilterCategoriesTree());

        $allBrands = Cache::remember('filter_brands_list_visible_catalog_v2', now()->addHours(1), fn() => $this->buildFilterBrandsList());

        return view('catalog.show', [
            'entity' => $brand,
            'isBrand' => true,
            'products' => $products,
            'categories' => $categoriesTree,
            'brands' => $allBrands,
            'currentCategory' => null,
            'currentSub' => null,
            'currentChild' => null,
            'backUrl' => route('brands.index'),
            'backLabel' => __('messages.nossas_marcas'),
            'breadcrumb' => [],
            'emptyMessage' => 'No se encontraron productos en esta marca.',
        ]);
    }
}
