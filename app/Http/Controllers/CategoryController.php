<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $search = $request->input('search', '');

        $cacheKey = "categories_index_{$page}_" . md5($search);

        // Buscamos os atributos globais (banners, logos, etc)
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return \DB::table('attributes')->first();
        });

        $categories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            return Category::where('status', 1)
                ->with(['subcategories.categoriasfilhas'])
                ->withCount([
                    'products' => function ($q) {
                        $q->where('status', 1);
                    },
                ])
                ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->paginate(20)
                ->withQueryString();
        });

        return view('categories.index', compact('categories', 'attribute'));
    }

    public function show(Category $category)
    {
        if ($category->status != 1) {
            abort(404);
        }

        $page = request()->get('page', 1);
        $cacheKey = "category_show_{$category->id}_{$page}";

        // 1. Buscamos o atributo global para os banners de fallback
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return \DB::table('attributes')->first();
        });

        // 2. Busca Categoria e Produtos (Cacheado)
        [$category, $products] = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($category) {
            $category = $category->load(['subcategories.categoriasfilhas']);
            $products = $category
                ->products()
                ->with(['brand', 'category']) // Eager loading para evitar N+1 no card
                ->where('status', 1)
                ->where('product_role', 'P')
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->paginate(12)
                ->withQueryString();

            return [$category, $products];
        });

        // 3. Dados para o Filtro Completo (Sidebar)
        // Carregamos a árvore inteira para que o componente mostre Cat > Sub > Filhas
        $categoriesTree = Cache::remember('filter_full_tree', now()->addHours(1), function () {
            return Category::where('status', 1)
                ->with(['subcategories.categoriasfilhas'])
                ->orderBy('name')
                ->get();
        });

        $brands = Cache::remember('filter_brands_list', now()->addHours(1), function () {
            return \App\Models\Brand::where('status', 1)->orderBy('name')->get();
        });

        $cartItems = auth()->check() ? auth()->user()->cart()->pluck('quantity', 'product_id')->toArray() : [];

        return view('categories.show', [
            'category' => $category,
            'products' => $products,
            'cartItems' => $cartItems,
            'attribute' => $attribute,
            'categories' => $categoriesTree, // Variável que o componente espera
            'brands' => $brands, // Variável que o componente espera
            'currentCategory' => $category, // Identificador para marcar ativo no menu
        ]);
    }
}
