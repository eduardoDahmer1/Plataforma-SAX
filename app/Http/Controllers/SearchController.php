<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use App\Models\Cart;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    /**
     * Index de Busca Otimizada
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 36);

        // Iniciamos a query filtrando apenas o que é ATIVO e tem ESTOQUE/FOTO
        $query = Product::query()
            ->select(['id', 'name', 'external_name', 'sku', 'price', 'stock', 'photo', 'brand_id', 'category_id', 'slug', 'status'])
            ->with(['brand:id,name'])
            ->where('status', 1)            // Apenas produtos ativos
            ->where('product_role', 'P')    // Solo productos padre en el buscador
            ->where('stock', '>', 0)        // Com estoque
            ->whereNotNull('photo')         // Com foto
            ->where('photo', '!=', '');

        // Filtro de Busca Textual
        if ($request->filled('search')) {
            $term = "%{$request->search}%";
            $query->where(function ($q) use ($term) {
                $q->where('external_name', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('sku', 'like', $term);
            });
        }

        // Filtros de Sidebar
        $query->when($request->brand, fn($q) => $q->where('brand_id', $request->brand))
            ->when($request->category, fn($q) => $q->where('category_id', $request->category))
            ->when($request->subcategory, fn($q) => $q->where('subcategory_id', $request->subcategory))
            ->when($request->categoriasfilhas, fn($q) => $q->where('categoriasfilhas_id', $request->categoriasfilhas));

        // Filtro de Preço
        $query->when($request->min_price, fn($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price));

        // Aplica Ordenação
        $this->applySorting($query, $request->sort_by);

        $paginated = $query->paginate($perPage)->withQueryString();

        // Dados da Sidebar - Agora filtrando apenas ATIVOS (Status 1)
        $sidebarData = Cache::remember('search_sidebar_v3', 3600, function () {
            $visibleProductFilter = function ($q) {
                $q->where('status', 1)
                    ->where('product_role', 'P')
                    ->where('stock', '>', 0)
                    ->whereNotNull('photo')
                    ->where('photo', '!=', '');
            };

            return [
                'brands'          => Brand::select('id', 'name')
                    ->where('status', 1) // Apenas marcas ativas
                    ->whereHas('products', $visibleProductFilter)
                    ->orderBy('name')
                    ->get(),
                'categories'      => Category::select('id', 'name', 'slug')
                    ->where('status', 1) // Apenas categorias ativas (sem restrição de ID fixo)
                    ->whereHas('products', $visibleProductFilter)
                    ->orderBy('name')
                    ->get(),
                'subcategories'   => Subcategory::select('id', 'name')
                    ->whereHas('products', $visibleProductFilter)
                    ->orderBy('name')
                    ->get(),
                'categoriasfilhas' => CategoriasFilhas::select('id', 'name')
                    ->whereHas('products', $visibleProductFilter)
                    ->orderBy('name')
                    ->get(),
            ];
        });

        // Carrinho
        $cartItems = [];
        if (auth()->check()) {
            $cartItems = Cart::where('user_id', auth()->id())->pluck('quantity', 'product_id')->toArray();
        }

        // Por isso (Mais seguro e explícito):
        return view('search.search', [
            'brands'           => $sidebarData['brands'],
            'categories'       => $sidebarData['categories'],
            'subcategories'    => $sidebarData['subcategories'],
            'categoriasfilhas' => $sidebarData['categoriasfilhas'], // Linha vital
            'paginated'        => $paginated,
            'cartItems'        => $cartItems,
            'request'          => $request,
            'query'            => $request->search
        ]);
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
            default      => $query->orderBy('id', 'desc'),
        };
    }
}
