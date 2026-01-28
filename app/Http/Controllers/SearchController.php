<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Cart;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    /**
     * Index de Busca Otimizada
     * Exceções: Oculta apenas produtos sem imagem OU sem estoque.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 35);
        
        // Chave de cache baseada nos filtros para máxima velocidade
        $cacheKey = 'search_filtered_v2_' . md5(json_encode($request->all()));

        $paginated = Cache::remember($cacheKey, 600, function () use ($request, $perPage) {
            $query = Product::query()
                ->select(['id', 'name', 'external_name', 'sku', 'price', 'stock', 'photo', 'brand_id', 'slug', 'status'])
                ->with(['brand:id,name']);

            /**
             * AS DUAS EXCEÇÕES SOLICITADAS:
             * 1. Precisa ter imagem (photo não nulo e não vazio)
             * 2. Precisa ter estoque (stock maior que zero)
             */
            $query->whereNotNull('photo')
                  ->where('photo', '!=', '')
                  ->where('stock', '>', 0);

            // Filtro de Busca Textual
            if ($request->filled('search')) {
                $term = "%{$request->search}%";
                $query->where(function($q) use ($term) {
                    $q->where('external_name', 'like', $term)
                      ->orWhere('name', 'like', $term)
                      ->orWhere('sku', 'like', $term);
                });
            }

            // Filtros de Sidebar
            $query->when($request->brand, fn($q) => $q->where('brand_id', $request->brand))
                  ->when($request->category, fn($q) => $q->where('category_id', $request->category))
                  ->when($request->subcategory, fn($q) => $q->where('subcategory_id', $request->subcategory))
                  ->when($request->childcategory, fn($q) => $q->where('childcategory_id', $request->childcategory));

            // Filtro de Preço
            $query->when($request->min_price, fn($q) => $q->where('price', '>=', $request->min_price))
                  ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price));

            // Aplica Ordenação
            $this->applySorting($query, $request->sort_by);

            // Paginação Nativa (Rápida)
            return $query->paginate($perPage)->withQueryString();
        });

        // Cache dos filtros da sidebar (1 hora)
        $sidebarData = Cache::remember('search_sidebar_v2', 3600, function() {
            return [
                'brands'          => Brand::select('id', 'name')->orderBy('name')->get(),
                'categories'      => Category::select('id', 'name')->orderBy('name')->get(),
                'subcategories'   => Subcategory::select('id', 'name')->orderBy('name')->get(),
                'childcategories' => Childcategory::select('id', 'name')->orderBy('name')->get(),
            ];
        });

        // Carrinho
        $cartItems = [];
        if (auth()->check()) {
            $cartItems = Cart::where('user_id', auth()->id())->pluck('quantity', 'product_id')->toArray();
        }

        return view('search.search', array_merge($sidebarData, [
            'paginated' => $paginated,
            'cartItems' => $cartItems,
            'query'     => $request->search
        ]));
    }

    /**
     * Lógica de Ordenação
     */
    private function applySorting($query, $sortBy)
    {
        match ($sortBy) {
            'latest'     => $query->orderBy('created_at', 'desc'),
            'oldest'     => $query->orderBy('created_at', 'asc'),
            'name_az'    => $query->orderBy('external_name', 'asc'),
            'name_za'    => $query->orderBy('external_name', 'desc'),
            'price_low'  => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            default      => $query->orderBy('updated_at', 'desc'),
        };
    }
}