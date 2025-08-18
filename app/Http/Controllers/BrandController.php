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

        $cacheKey = "brands_index_{$page}_".md5($search);

        $brands = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Brand::orderBy('name');

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->paginate(12)->withQueryString();
        });

        return view('brands.index', compact('brands'));
    }

    // Mostra marca específica pelo slug
    public function publicShow($slug)
    {
        $cacheKey = "brand_show_{$slug}";

        $brand = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($slug) {
            return Brand::where('slug', $slug)->firstOrFail();
        });

        return view('brands.show', compact('brand'));
    }
}
