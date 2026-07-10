<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\SlugRedirect;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function publicIndex(Request $request)
    {
        $page = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "brands_index_{$page}_" . md5($search);

        $brands = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Brand::where('status', 1)
                ->whereHas('products', fn($q) => $this->applyActiveProductScope($q))
                ->withCount([
                    'products as active_products_count' => fn($q) => $this->applyActiveProductScope($q),
                ])
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
        $cacheKey = "brand_show_{$slug}_page_{$page}";

        try {
            $brand = Cache::remember("brand_{$slug}", now()->addMinutes(30), function () use ($slug) {
                return Brand::where('slug', $slug)
                    ->where('status', 1)
                    ->whereHas('products', fn($q) => $this->applyActiveProductScope($q))
                    ->firstOrFail();
            });
        } catch (ModelNotFoundException $e) {
            if ($redirectUrl = SlugRedirect::resolveUrl('brand', $slug)) {
                return redirect($redirectUrl, 301);
            }
            throw $e;
        }

        $products = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($brand) {
            return $brand
                ->products()
                ->where('status', 1)
                ->where('product_role', 'P')
                ->where('stock', '>', 0)
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->with(['brand', 'category'])
                ->latest()
                ->paginate(12)
                ->withQueryString();
        });

        $categoriesTree = Cache::remember('filter_full_tree_active', now()->addHours(1), fn() => $this->buildFilterCategoriesTree());

        $allBrands = Cache::remember('filter_brands_list_active', now()->addHours(1), fn() => $this->buildFilterBrandsList());

        return view('catalog.show', [
            'entity' => $brand,
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
