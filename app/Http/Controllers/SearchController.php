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
        $search = $request->search;

        // 1. Base da query principal
        $query = Product::query()
            ->select([
                'id', 'name', 'external_name', 'sku', 'price', 'stock', 
                'photo', 'brand_id', 'category_id', 'subcategory_id', 
                'childcategory_id', 'slug', 'status'
            ])
            ->with(['brand:id,name'])
            ->where('status', 1)
            ->where('product_role', 'P')
            ->where('stock', '>', 0)
            ->whereNotNull('photo')
            ->where('photo', '!=', '');

        // Filtro de Busca Textual
        if ($request->filled('search')) {
            $term = "%{$search}%";
            $query->where(function ($q) use ($term) {
                $q->where('external_name', 'like', $term)
                  ->orWhere('name', 'like', $term)
                  ->orWhere('sku', 'like', $term);
            });
        }

        // 2. Sidebar Dinâmica
        $sidebarQuery = clone $query;
        $productIds = (clone $sidebarQuery)->pluck('id');

        // Marcas e Categorias geralmente têm status, se der erro nelas também, remova o ->where('status', 1)
        $brands = Brand::where('status', 1) 
            ->whereHas('products', function($q) use ($productIds) {
                $q->whereIn('id', $productIds);
            })->orderBy('name')->get(['id', 'name']);

        $categories = Category::where('status', 1)
            ->whereHas('products', function($q) use ($productIds) {
                $q->whereIn('id', $productIds);
            })->orderBy('name')->get(['id', 'name', 'slug']);

        // Removido 'status' de Subcategories e CategoriasFilhas conforme o erro da imagem
        $subcategories = Subcategory::whereHas('products', function($q) use ($productIds) {
                $q->whereIn('id', $productIds);
            })->orderBy('name')->get(['id', 'name']);

        $categoriasfilhas = CategoriasFilhas::whereHas('products', function($q) use ($productIds) {
                $q->whereIn('id', $productIds);
            })->orderBy('name')->get(['id', 'name']);

        // 3. Aplicar filtros selecionados
        $query->when($request->brand, fn($q) => $q->where('brand_id', $request->brand))
            ->when($request->category, fn($q) => $q->where('category_id', $request->category))
            ->when($request->subcategory, fn($q) => $q->where('subcategory_id', $request->subcategory))
            ->when($request->categoriasfilhas, fn($q) => $q->where('childcategory_id', $request->categoriasfilhas))
            ->when($request->min_price, fn($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price));

        $this->applySorting($query, $request->sort_by);

        return view('search.search', [
            'brands'           => $brands,
            'categories'       => $categories,
            'subcategories'    => $subcategories,
            'categoriasfilhas' => $categoriasfilhas,
            'paginated'        => $query->paginate($perPage)->withQueryString(),
            'request'          => $request,
            'query'            => $search
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
    
    public function autocomplete(Request $request)
    {
        try {
            $search = $request->get('q');

            if (empty($search) || strlen($search) < 2) {
                return response()->json([]);
            }

            $term = "%{$search}%";

            $products = Product::query()
                ->select(['id', 'name', 'external_name', 'sku', 'price', 'photo', 'slug', 'brand_id', 'category_id'])
                ->with(['brand:id,name', 'category:id,name'])
                ->where('status', 1)
                ->where('product_role', 'P')
                ->where('stock', '>', 0)
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->where(function ($q) use ($term) {
                    // A ordem aqui no WHERE não afeta o resultado final, 
                    // mas mantive o name primeiro por organização
                    $q->where('name', 'like', $term)
                    ->orWhere('external_name', 'like', $term)
                    ->orWhere('sku', 'like', $term);
                })
                /* 
                ORDENAÇÃO PRIORITÁRIA:
                1. Primeiro os que o 'name' começa exatamente com o que foi digitado
                2. Depois por ordem alfabética do name
                */
                ->orderByRaw("CASE 
                    WHEN name LIKE ? THEN 1 
                    WHEN name LIKE ? THEN 2 
                    ELSE 3 
                    END", [$search, $search . '%'])
                ->orderBy('name', 'asc')
                ->limit(50) // Aumentado para 50 produtos
                ->get();

            $results = $products->map(function($product) {
                // Ajuste de URL da foto
                $photoPath = (str_contains($product->photo, 'http')) 
                    ? $product->photo 
                    : asset('storage/' . $product->photo);

                return [
                    // Exibe o external_name se existir, senão o name, mas a busca priorizou o name
                    'name'     => $product->name ?? $product->external_name,
                    'sku'      => $product->sku,
                    'price'    => number_format($product->price, 2, '.', ','),
                    'photo'    => $photoPath,
                    'brand'    => $product->brand->name ?? 'SAX',
                    'category' => $product->category->name ?? '',
                    'url'      => route('produto.show', $product->slug) 
                ];
            });

            return response()->json($results);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
