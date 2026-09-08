<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use App\Models\SlugRedirect;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\VisibleCatalogProductsService;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "subcategories_index_{$page}_" . md5($search);

        // Carrega atributos globais para o index
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        $subcategories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Subcategory::with('category')
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('status', 1))
                ->orderBy('name');

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->paginate(20)->withQueryString();
        });

        return view('subcategories.index', compact('subcategories', 'attribute'));
    }

    public function show(Request $request, $idOrSlug)
    {
        $page = $request->get('page', 1);
        $sortBy = $this->catalogSortBy($request);
        $perPage = $this->catalogPerPage($request);
        $cacheKey = "subcategory_show_visible_catalog_v2_{$idOrSlug}_page_{$page}_{$sortBy}_{$perPage}";

        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($idOrSlug, $sortBy, $perPage) {
                $subcategory = Subcategory::with(['category', 'categoriasfilhas'])
                    ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('status', 1))
                    ->where(fn ($query) => $query
                        ->where('slug', $idOrSlug)
                        ->orWhere('id', $idOrSlug))
                    ->firstOrFail();

                $productsQuery = VisibleCatalogProductsService::builder()
                    ->where('products.subcategory_id', $subcategory->id)
                    ->with(['brand', 'category', 'translations']);
                $this->applyCatalogSorting($productsQuery, $sortBy);
                $products = $productsQuery
                    ->paginate($perPage)
                    ->withQueryString();

                return [
                    'subcategory' => $subcategory,
                    'products' => $products,
                ];
            });
        } catch (ModelNotFoundException $e) {
            if ($redirectUrl = SlugRedirect::resolveUrl('subcategory', $idOrSlug)) {
                return redirect($redirectUrl, 301);
            }
            throw $e;
        }

        $allCategories = Cache::remember('filter_full_tree_visible_catalog_v2', now()->addHours(1), fn() => $this->buildFilterCategoriesTree());

        $brands = Cache::remember(
            "filter_brands_subcategory_visible_catalog_v1_{$data['subcategory']->id}",
            now()->addHours(1),
            fn() => $this->buildFilterBrandsList(
                fn($query) => $query->where('products.subcategory_id', $data['subcategory']->id)
            )
        );

        return view('catalog.show', [
            'entity' => $data['subcategory'],
            'products' => $data['products'],
            'attribute' => $attribute,
            'categories' => $allCategories,
            'brands' => $brands,
            'currentCategory' => $data['subcategory']->category,
            'currentSub' => $data['subcategory'],
            'currentChild' => null,
            'backUrl' => route('subcategories.index'),
            'backLabel' => __('VOLVER A SUBCATEGORIAS'),
            'breadcrumb' => $data['subcategory']->category ? [
                ['label' => $data['subcategory']->category->name, 'url' => route('categories.show', $data['subcategory']->category->slug)],
            ] : [],
            'emptyMessage' => 'No se encontraron productos en esta subcategoría.',
        ]);
    }
}
