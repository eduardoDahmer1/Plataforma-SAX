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

    public function publicShow($slug, Request $request)
    {
        $page = $request->get('page', 1);
        $cacheKey = "brand_show_{$slug}_page_{$page}";

        // 1. Busca a marca com cache (O campo 'banner' virá automaticamente aqui)
        $brand = Cache::remember("brand_{$slug}", now()->addMinutes(30), function () use ($slug) {
            return Brand::where('slug', $slug)->firstOrFail();
        });

        // 2. Busca produtos...
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
