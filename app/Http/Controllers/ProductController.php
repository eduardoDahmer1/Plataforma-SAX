<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Home: Exibe apenas produtos ativos (Role 'P')
     */
    public function home(Request $request)
    {
        $search  = $request->get('search');
        $page    = $request->get('page', 1);
        $perPage = 40;

        // Cache simplificado apenas para produtos
        $cacheKey = "home_products_{$page}_" . md5($search ?? '');

        $items = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search) {
            $columns = [
                'id',
                'external_name',
                'sku',
                'price',
                'photo',
                'brand_id',
                'category_id',
                'created_at',
                'slug',
                'product_role'
            ];

            return Product::select($columns)
                ->where('product_role', 'P') // Filtra apenas produtos "Pai" para a vitrine
                ->when($search, function ($q) use ($search) {
                    $q->where('external_name', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%");
                })
                ->with(['cupons' => fn($q) => $q->ativos()])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($p) => (object)[
                    'id'          => $p->id,
                    'title'       => $p->external_name,
                    'description' => $p->sku,
                    'photo'       => $p->photo,
                    'price'       => $p->price,
                    'price_final' => $this->calcularPrecoComCupon($p),
                    'type'        => 'product',
                    'slug'        => $p->slug,
                    'created_at'  => $p->created_at,
                ]);
        });

        // Paginação manual para manter a estrutura da sua View
        $pagedItems = $items->forPage($page, $perPage);

        return view('home', [
            'items'    => $pagedItems,
            'page'     => $page,
            'lastPage' => ceil($items->count() / $perPage),
        ]);
    }

    /**
     * Index de Produtos (Listagem Geral)
     */
    public function index(Request $request)
    {
        $search   = $request->get('search');
        $page     = $request->get('page', 1);
        $perPage  = 12;
        $cacheKey = "products_page_{$page}_" . md5($search ?? '');

        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search, $perPage) {
            return Product::with(['cupons' => fn($q) => $q->ativos()])
                ->where('product_role', 'P') // Apenas pais na listagem
                ->when($search, fn($q) => $q->where('external_name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%"))
                ->orderByDesc('id')
                ->paginate($perPage);
        });

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products'));
    }

    public function show($id_or_slug)
    {
        // Busca com relações para evitar N+1 queries
        $product = Product::where('id', $id_or_slug)
            ->orWhere('slug', $id_or_slug)
            ->with(['brand', 'category', 'subcategory', 'childcategory'])
            ->firstOrFail();

        // 1. Lógica de Preço (Banco: price, previous_price, promotion_price)
        // Se houver preço promocional, ele assume o lugar do preço principal
        $product->current_price = $product->promotion_price > 0 ? $product->promotion_price : $product->price;
        $product->has_discount = $product->previous_price > $product->current_price;

        // 2. Identificar o "Pai" para listar tamanhos (Variantes de Tamanho)
        $masterId = $product->id;
        if ($product->product_role === 'F' && !empty($product->parent_id)) {
            $parentIds = is_array($product->parent_id) ? $product->parent_id : explode(',', $product->parent_id);
            $masterId = trim($parentIds[0]);
        }

        // Busca irmãos (mesmo pai) para os seletores de tamanho
        $siblings = Product::where('parent_id', 'LIKE', "%{$masterId}%")
            ->orWhere('id', $masterId)
            ->where('status', 1)
            ->get();

        // 3. Busca Cores relacionadas (Baseado na sua coluna color_parent_id)
        $colorIds = [];
        if (!empty($product->color_parent_id)) {
            $colorIds = is_array($product->color_parent_id) ? $product->color_parent_id : explode(',', $product->color_parent_id);
        }
        $coresRelacionadas = Product::whereIn('id', $colorIds)->where('status', 1)->get();

        return view('produtos.show', [
            'product'           => $product,
            'siblings'          => $siblings,
            'coresRelacionadas' => $coresRelacionadas
        ]);
    }

    /**
     * Listagem por Categorias
     */
    public function byCategory(Category $category)
    {
        $products = Product::where('category_id', $category->id)
            ->where('product_role', 'P')
            ->with(['cupons' => fn($q) => $q->ativos()])
            ->paginate(12);

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products', 'category'));
    }

    /**
     * Listagem por Subcategorias
     */
    public function bySubcategory(Subcategory $subcategory)
    {
        $products = Product::where('subcategory_id', $subcategory->id)
            ->where('product_role', 'P')
            ->with(['cupons' => fn($q) => $q->ativos()])
            ->paginate(12);

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products', 'subcategory'));
    }

    /**
     * Listagem por Childcategory
     */
    public function byChildcategory(Childcategory $childcategory)
    {
        $products = Product::where('childcategory_id', $childcategory->id)
            ->where('product_role', 'P')
            ->with(['cupons' => fn($q) => $q->ativos()])
            ->paginate(12);

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products', 'childcategory'));
    }

    /**
     * Helper: Calcula o preço considerando o melhor cupom ativo
     */
    private function calcularPrecoComCupon(Product $p)
    {
        $cupons = $p->cupons ?? collect();
        if ($cupons->isEmpty()) {
            return $p->price;
        }

        $descontoMax = $cupons->max(fn($c) => $c->tipo === 'percentual'
            ? $p->price * ($c->montante / 100)
            : $c->montante);

        return $descontoMax ? max(0, $p->price - $descontoMax) : $p->price;
    }
}
