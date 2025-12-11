<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    // Lista marcas públicas com busca e paginação
    public function publicIndex(Request $request)
    {
        $page   = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "brands_index_{$page}_" . md5($search);

        $brands = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Brand::withCount('products')
                ->orderBy('name')
                ->having('products_count', '>', 0); // << AQUI

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->paginate(20)->withQueryString();
        });

        return view('brands.index', compact('brands'));
    }

    // Mostra marca específica pelo slug
    public function publicShow($slug, Request $request)
    {
        $page = $request->get('page', 1);
        $cacheKey = "brand_show_{$slug}_page_{$page}";

        // Marca em cache
        $brand = Cache::remember("brand_{$slug}", now()->addMinutes(30), function () use ($slug) {
            return Brand::where('slug', $slug)->firstOrFail();
        });

        // Produtos com imagem
        $products = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($brand) {
            // Pega todos os produtos, filtra os que têm foto no storage
            $filtered = $brand->products()->with('brand')->get()->filter(function ($product) {
                return $product->photo && \Storage::disk('public')->exists($product->photo);
            });

            // Paginação manual depois do filter
            $page = request()->get('page', 1);
            $perPage = 12;
            $offset = ($page - 1) * $perPage;
            $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $filtered->slice($offset, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return $paginated;
        });

        return view('brands.show', compact('brand', 'products'));
    }
}
