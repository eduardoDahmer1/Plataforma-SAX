<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Attribute; // Certifique-se de que o Model Attribute existe
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $page   = $request->get('page', 1);
        $search = $request->input('search', '');

        $cacheKey = "categories_index_{$page}_" . md5($search);

        // Buscamos os atributos globais (banners, logos, etc)
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return \DB::table('attributes')->first(); 
        });

        $categories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            return Category::where('status', 1)
                ->with(['subcategories.categoriasfilhas'])
                ->withCount(['products' => function ($q) {
                    $q->where('status', 1);
                }])
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

        // 2. Busca Categoria e Produtos
        [$category, $products] = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($category) {
            $category = $category->load(['subcategories.categoriasfilhas']);
            $products = $category->products()
                ->with('brand') // Adicionado para evitar erro no layout novo
                ->where('status', 1)
                ->where('product_role', 'P')
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->paginate(12)
                ->withQueryString();

            return [$category, $products];
        });

        $cartItems = auth()->check()
            ? auth()->user()->cart()->pluck('quantity', 'product_id')->toArray()
            : [];

        // Passamos o 'attribute' para a view
        return view('categories.show', compact('category', 'products', 'cartItems', 'attribute'));
    }
}