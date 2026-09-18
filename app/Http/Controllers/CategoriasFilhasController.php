<?php

namespace App\Http\Controllers;

use App\Models\CategoriasFilhas;
use App\Models\SlugRedirect;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\VisibleCatalogProductsService;
use App\Services\StoreControlService;
use App\Services\StoreTaxonomyService;

class CategoriasFilhasController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $search = $request->get('search', '');
        $profile = app(StoreControlService::class)->storeProfile();
        $cacheKey = "categorias_filhas_index_v2_{$profile}_{$page}_" . md5($search);

        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        $categoriasfilhas = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = app(StoreTaxonomyService::class)->childCategories(
                CategoriasFilhas::with('subcategory.category')
            )
                ->whereHas('subcategory.category', fn ($categoryQuery) => $categoryQuery->where('status', 1))
                ->orderBy('name');
            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }
            return $query->paginate(20)->withQueryString();
        });

        return view('categoriasfilhas.index', compact('categoriasfilhas', 'attribute'));
    }

    public function show(Request $request, $idOrSlug)
    {
        $page = $request->get('page', 1);
        $sortBy = $this->catalogSortBy($request);
        $perPage = $this->catalogPerPage($request);
        $profile = app(StoreControlService::class)->storeProfile();
        $assignmentsCacheVersion = Cache::get('product_category_assignments_cache_version', 'initial');
        $cacheKey = "cat_filha_show_visible_catalog_v3_{$profile}_{$idOrSlug}_p{$page}_{$sortBy}_{$perPage}_{$assignmentsCacheVersion}";

        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($idOrSlug, $sortBy, $perPage) {
                $categoriasfilhas = app(StoreTaxonomyService::class)->childCategories(
                    CategoriasFilhas::with(['subcategory.category'])
                )
                    ->whereHas('subcategory.category', fn ($categoryQuery) => $categoryQuery->where('status', 1))
                    ->where(fn ($query) => $query
                        ->where('slug', $idOrSlug)
                        ->orWhere('id', $idOrSlug))
                    ->firstOrFail();

                $productsQuery = VisibleCatalogProductsService::builder()
                    ->where(function ($query) use ($categoriasfilhas) {
                        $query->where('products.childcategory_id', $categoriasfilhas->id)
                            ->orWhereHas('additionalCategories', fn ($assignmentQuery) => $assignmentQuery
                                ->where('childcategory_id', $categoriasfilhas->id));
                    })
                    ->with(['brand', 'category', 'translations']);
                $this->applyCatalogSorting($productsQuery, $sortBy);
                $products = $productsQuery
                    ->paginate($perPage)
                    ->withQueryString();

                return [
                    'categoriasfilhas' => $categoriasfilhas,
                    'products' => $products,
                ];
            });
        } catch (ModelNotFoundException $e) {
            if ($redirectUrl = SlugRedirect::resolveUrl('categoria_filha', $idOrSlug)) {
                return redirect($redirectUrl, 301);
            }
            throw $e;
        }

        $categoriesTree = Cache::remember("filter_full_tree_visible_catalog_v3_{$profile}", now()->addHours(1), fn() => $this->buildFilterCategoriesTree());

        $brands = Cache::remember(
            "filter_brands_child_category_visible_catalog_v1_{$data['categoriasfilhas']->id}_{$assignmentsCacheVersion}",
            now()->addHours(1),
            fn() => $this->buildFilterBrandsList(
                fn($query) => $query->where(function ($productQuery) use ($data) {
                    $productQuery->where('products.childcategory_id', $data['categoriasfilhas']->id)
                        ->orWhereHas('additionalCategories', fn ($assignmentQuery) => $assignmentQuery
                            ->where('childcategory_id', $data['categoriasfilhas']->id));
                })
            )
        );

        return view('catalog.show', [
            'entity' => $data['categoriasfilhas'],
            'products' => $data['products'],
            'attribute' => $attribute,
            'categories' => $categoriesTree,
            'brands' => $brands,
            'currentCategory' => $data['categoriasfilhas']->subcategory->category ?? null,
            'currentSub' => $data['categoriasfilhas']->subcategory,
            'currentChild' => $data['categoriasfilhas'],
            'backUrl' => route('categorias-filhas.index'),
            'backLabel' => 'VOLVER A CATEGORIAS FILHAS',
            'breadcrumb' => array_filter([
                $data['categoriasfilhas']->subcategory->category ?? null
                    ? ['label' => $data['categoriasfilhas']->subcategory->category->name, 'url' => route('categories.show', $data['categoriasfilhas']->subcategory->category->slug)]
                    : null,
                $data['categoriasfilhas']->subcategory ?? null
                    ? ['label' => $data['categoriasfilhas']->subcategory->name, 'url' => route('subcategories.show', $data['categoriasfilhas']->subcategory->slug)]
                    : null,
            ]),
            'emptyMessage' => 'No se encontraron productos en esta categoría.',
        ]);
    }
}
