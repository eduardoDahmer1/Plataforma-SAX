<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    /**
     * Lista marcas públicas com busca e paginação.
     * O 'internal_banner' pode ser usado aqui se você quiser exibir
     * cards diferenciados na listagem geral.
     */
    public function publicIndex(Request $request)
    {
        $page = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "brands_index_{$page}_" . md5($search);

        $brands = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Brand::where('status', 1) // Adicionado: Somente marcas ativas
                ->withCount([
                    'products' => function ($q) {
                        $q->where('status', 1)->where('product_role', 'P');
                    },
                ])
                ->orderBy('name')
                ->having('products_count', '>', 0);

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->paginate(20)->withQueryString();
        });

        return view('brands.index', compact('brands'));
    }

    /**
     * Exibe o perfil da marca e seus produtos.
     * Agora o objeto $brand carrega 'banner' e 'internal_banner'.
     */
    public function publicShow($slug, Request $request)
    {
        $page = $request->get('page', 1);
        $cacheKey = "brand_show_{$slug}_page_{$page}";

        // 1. Busca a marca principal
        $brand = Cache::remember("brand_{$slug}", now()->addMinutes(30), function () use ($slug) {
            return Brand::where('slug', $slug)->where('status', 1)->firstOrFail();
        });

        // 2. Busca produtos da marca com paginação e cache
        $products = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($brand) {
            return $brand
                ->products()
                ->where('status', 1)
                ->where('product_role', 'P')
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->with(['brand', 'category'])
                ->latest()
                ->paginate(12)
                ->withQueryString();
        });

        // --- FILTRO COMPLETO (ARVORE COMPLETA) ---
        // 3. Carrega Categorias, Subcategorias e Categorias Filhas em uma única query com cache
        $categoriesTree = Cache::remember('filter_full_tree', now()->addHours(1), function () {
            return Category::where('status', 1)
                ->with(['subcategories.categoriasfilhas']) // Carrega toda a hierarquia
                ->orderBy('name')
                ->get();
        });

        // 4. Carrega todas as Marcas para o filtro lateral
        $allBrands = Cache::remember('filter_brands_list', now()->addHours(1), function () {
            return Brand::where('status', 1)->orderBy('name')->get();
        });
        // -----------------------------------------

        return view('brands.show', [
            'brand' => $brand,
            'products' => $products,
            'categories' => $categoriesTree, // Enviando a árvore completa
            'brands' => $allBrands, // Enviando a lista de marcas
        ]);
    }
}
