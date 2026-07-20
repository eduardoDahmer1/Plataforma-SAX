<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use App\Models\SlugRedirect;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            $query = Subcategory::with('category')->orderBy('name');

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
        $cacheKey = "subcategory_show_{$idOrSlug}_page_{$page}_{$sortBy}_{$perPage}";

        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($idOrSlug, $sortBy, $perPage) {
                $subcategory = Subcategory::with(['category', 'categoriasfilhas'])
                    ->where('slug', $idOrSlug)
                    ->orWhere('id', $idOrSlug)
                    ->firstOrFail();

                $productsQuery = $subcategory
                    ->products()
                    ->where('status', 1)
                    ->where('is_outlet', false)
                    ->where('product_role', 'P')
                    ->where('stock', '>', 0)
                    ->whereNotNull('photo')
                    ->where('photo', '!=', '')
                    ->with(['brand', 'category']);
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

        $allCategories = Cache::remember('filter_full_tree_active', now()->addHours(1), fn() => $this->buildFilterCategoriesTree());

        $brands = Cache::remember('filter_brands_list_active', now()->addHours(1), fn() => $this->buildFilterBrandsList());

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
