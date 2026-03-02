<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $page   = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "subcategories_index_{$page}_" . md5($search);

        // Carrega atributos globais para o index
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        $subcategories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Subcategory::with('category')->orderBy('name');

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->paginate(20)->withQueryString();
        });

        return view('subcategories.index', compact('subcategories', 'attribute'));
    }

    public function show(Request $request, $idOrSlug)
    {
        $page = $request->get('page', 1);
        // Cache diferenciado por slug/id e página
        $cacheKey = "subcategory_show_{$idOrSlug}_page_{$page}";

        // 1. Carrega os atributos globais (Banners de fallback)
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        // 2. Busca os dados com suporte a ID ou Slug (Resolve o 404 da sua imagem)
        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($idOrSlug) {
            $subcategory = Subcategory::with(['category', 'categoriasfilhas'])
                ->where('slug', $idOrSlug)
                ->orWhere('id', $idOrSlug)
                ->firstOrFail();

            // Pagina os produtos com a marca para o layout estilo JW
            $products = $subcategory->products()
                ->where('status', 1)
                ->with('brand') 
                ->paginate(12)
                ->withQueryString();

            return [
                'subcategory' => $subcategory,
                'products'    => $products
            ];
        });

        return view('subcategories.show', [
            'subcategory' => $data['subcategory'],
            'products'    => $data['products'],
            'attribute'   => $attribute 
        ]);
    }
}