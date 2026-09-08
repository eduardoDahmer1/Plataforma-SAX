<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SlugRedirect;
use App\Services\VisibleCatalogProductsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $search = $request->input('search', '');

        $cacheKey = "categories_index_visible_catalog_v2_{$page}_" . md5($search);

        // Buscamos os atributos globais (banners, logos, etc)
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return \DB::table('attributes')->first();
        });

        $categories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            return Category::where('status', 1)
                ->with(['subcategories.categoriasfilhas'])
                ->withCount([
                    'products' => fn ($q) => $this->applyActiveProductScope($q),
                ])
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->paginate(20)
                ->withQueryString();
        });

        return view('categories.index', compact('categories', 'attribute'));
    }

    public function show(Request $request, $slug)
    {
        try {
            $category = Category::where('slug', $slug)->orWhere('id', $slug)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            if ($redirectUrl = SlugRedirect::resolveUrl('category', $slug)) {
                return redirect($redirectUrl, 301);
            }
            throw $e;
        }

        if ($category->status != 1) {
            abort(404);
        }

        $page = $request->get('page', 1);
        $sortBy = $this->catalogSortBy($request);
        $perPage = $this->catalogPerPage($request);
        $cacheKey = "category_show_visible_catalog_v2_{$category->id}_{$page}_{$sortBy}_{$perPage}";

        // 1. Buscamos o atributo global para os banners de fallback
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return \DB::table('attributes')->first();
        });

        // 2. Busca Categoria e Produtos (Cacheado)
        [$category, $products] = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($category, $sortBy, $perPage) {
            $category = $category->load(['subcategories.categoriasfilhas']);
            $productsQuery = VisibleCatalogProductsService::builder()
                ->where('products.category_id', $category->id)
                ->with(['brand', 'category', 'translations']); // Eager loading para evitar N+1 no card
            $this->applyCatalogSorting($productsQuery, $sortBy);
            $products = $productsQuery
                ->paginate($perPage)
                ->withQueryString();

            return [$category, $products];
        });

        // 3. Dados para o Filtro Completo (Sidebar)
        // Carregamos a árvore inteira para que o componente mostre Cat > Sub > Filhas
        $categoriesTree = Cache::remember('filter_full_tree_visible_catalog_v2', now()->addHours(1), fn() => $this->buildFilterCategoriesTree());

        $brands = Cache::remember(
            "filter_brands_category_visible_catalog_v1_{$category->id}",
            now()->addHours(1),
            fn() => $this->buildFilterBrandsList(
                fn($query) => $query->where('products.category_id', $category->id)
            )
        );

        $cartItems = auth()->check() ? auth()->user()->cart()->pluck('quantity', 'product_id')->toArray() : [];

        return view('catalog.show', [
            'entity' => $category,
            'products' => $products,
            'cartItems' => $cartItems,
            'attribute' => $attribute,
            'categories' => $categoriesTree,
            'brands' => $brands,
            'currentCategory' => $category,
            'currentSub' => null,
            'currentChild' => null,
            'backUrl' => route('categories.index'),
            'backLabel' => __('messages.voltar_categorias'),
            'breadcrumb' => [],
            'emptyMessage' => 'No se encontraron productos en esta categoría.',
        ]);
    }
}
