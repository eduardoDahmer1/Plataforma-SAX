<?php

namespace App\Http\Controllers;

use App\Models\Brand;
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
        $page   = $request->get('page', 1);
        $search = $request->get('search', '');

        // Chave de cache única por busca e página
        $cacheKey = "brands_index_{$page}_" . md5($search);

        $brands = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Brand::withCount('products')
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

        // 1. Busca a marca com cache. 
        // O campo 'internal_banner' agora faz parte da model recuperada.
        $brand = Cache::remember("brand_{$slug}", now()->addMinutes(30), function () use ($slug) {
            return Brand::where('slug', $slug)->firstOrFail();
        });

        // 2. Busca produtos da marca
        $products = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($brand) {
            return $brand->products()
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->with(['brand', 'category'])
                ->latest()
                ->paginate(12)
                ->withQueryString();
        });

        return view('brands.show', compact('brand', 'products'));
    }
}