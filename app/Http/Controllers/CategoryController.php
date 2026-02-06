<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $page   = $request->get('page', 1);
        $search = $request->input('search', '');

        $cacheKey = "categories_index_{$page}_" . md5($search);

        $categories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            return Category::where('status', 1) // Somente categorias ativas
                ->with(['subcategories.childcategories'])
                ->withCount(['products' => function ($q) {
                    $q->where('status', 1); // Conta apenas produtos ativos
                }])
                ->when(
                    $search,
                    fn($q) =>
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    })
                )
                ->paginate(20)
                ->withQueryString();
        });

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        // 1. Verificação de Segurança
        if ($category->status != 1) {
            abort(404, 'Categoria não encontrada ou inativa.');
        }

        $page = request()->get('page', 1);
        $cacheKey = "category_show_{$category->id}_{$page}";

        // 2. Busca com Cache
        [$category, $products] = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($category) {
            // REMOVIDO o filtro de status da subcategory pois a coluna não existe no seu banco
            $category = $category->load(['subcategories.childcategories']);

            // Mantemos o filtro nos produtos (assumindo que a tabela products tem status)
            $products = $category->products()
                ->where('status', 1)
                ->paginate(12)
                ->withQueryString();

            return [$category, $products];
        });

        // 3. Itens do carrinho
        $cartItems = auth()->check()
            ? auth()->user()->cart()->pluck('quantity', 'product_id')->toArray()
            : [];

        return view('categories.show', compact('category', 'products', 'cartItems'));
    }
}
