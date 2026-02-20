<?php

namespace App\Http\Controllers;

use App\Models\CategoriasFilhas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoriasFilhasController extends Controller
{
    public function index(Request $request)
    {
        $page   = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "categorias-filhas_index_{$page}_" . md5($search);

        $categoriasfilhas = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = CategoriasFilhas::with('subcategory.category')->orderBy('name');

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->paginate(20)->withQueryString();
        });

        return view('categoriasfilhas.index', compact('categoriasfilhas'));
    }

    public function show(Request $request, $slug)
    {
        $page = $request->get('page', 1);
        $cacheKey = "categoriasfilhas_show_{$slug}_page_{$page}";

        // Buscamos a categoria e os produtos paginados dentro do cache
        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($slug) {
            $categoriasfilhas = CategoriasFilhas::with(['subcategory.category'])
                ->where('slug', $slug)
                ->firstOrFail();

            // Pagina os produtos relacionados
            $products = $categoriasfilhas->products()->paginate(24)->withQueryString();

            return [
                'categoriasfilhas' => $categoriasfilhas,
                'products' => $products
            ];
        });

        return view('categoriasfilhas.show', [
            'categoriasfilhas' => $data['categoriasfilhas'],
            'products'      => $data['products']
        ]);
    }
}
