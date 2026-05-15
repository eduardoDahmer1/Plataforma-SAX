<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
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
        $cacheKey = "subcategory_show_{$idOrSlug}_page_{$page}";

        // 1. Atributos globais
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        // 2. Dados da subcategoria e produtos
        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($idOrSlug) {
            $subcategory = Subcategory::with(['category', 'categoriasfilhas'])
                ->where('slug', $idOrSlug)
                ->orWhere('id', $idOrSlug)
                ->firstOrFail();

            $products = $subcategory
                ->products()
                ->where('status', 1)
                ->where('product_role', 'P')
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->with(['brand', 'category'])
                ->paginate(12)
                ->withQueryString();

            return [
                'subcategory' => $subcategory,
                'products' => $products,
            ];
        });

        // 3. Dados para o Filtro Completo (Sidebar)
        // Buscamos todas as categorias com suas subcategorias e categorias filhas para o menu lateral
        $allCategories = Cache::remember('filter_full_tree', now()->addHours(1), function () {
            return Category::where('status', 1)
                ->with(['subcategories.categoriasfilhas'])
                ->orderBy('name')
                ->get();
        });

        $brands = Cache::remember('filter_brands_list', now()->addHours(1), function () {
            return Brand::where('status', 1)->orderBy('name')->get();
        });

        return view('subcategories.show', [
            'subcategory' => $data['subcategory'],
            'products' => $data['products'],
            'attribute' => $attribute,
            'categories' => $allCategories, // Árvore completa: Cat > Sub > Filha
            'brands' => $brands,
            // Facilitadores para o componente saber onde estamos
            'currentSubcategory' => $data['subcategory'],
            'currentCategory' => $data['subcategory']->category,
        ]);
    }
}
