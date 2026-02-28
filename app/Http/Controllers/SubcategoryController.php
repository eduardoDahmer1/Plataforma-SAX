<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $page   = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "subcategories_index_{$page}_" . md5($search);

        $subcategories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Subcategory::with('category')->orderBy('name');

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->paginate(20)->withQueryString();
        });

        return view('subcategories.index', compact('subcategories'));
    }

    public function show(Request $request, $slug)
    {
        $page = $request->get('page', 1);
        // Cache diferenciado por slug e página
        $cacheKey = "subcategory_show_{$slug}_page_{$page}";

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($slug) {
            $subcategory = Subcategory::with(['category', 'categoriasfilhas'])
                ->where('slug', $slug)
                ->firstOrFail();

            // Pagina os produtos relacionados a esta subcategoria
            $products = $subcategory->products()
                ->with('brand') // Carrega a marca para evitar o erro de N+1 no layout novo
                ->paginate(12)  // Define quantos produtos por página (ex: 12)
                ->withQueryString();

            return [
                'subcategory' => $subcategory,
                'products' => $products
            ];
        });

        return view('subcategories.show', [
            'subcategory' => $data['subcategory'],
            'products' => $data['products']
        ]);
    }
}
