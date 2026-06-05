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
use Illuminate\Support\Facades\Storage;

class CategoriasFilhasController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $search = $request->get('search', '');
        $cacheKey = "categorias_filhas_index_{$page}_" . md5($search);

        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        $categoriasfilhas = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = CategoriasFilhas::with('subcategory.category')->orderBy('name');
            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }
            return $query->paginate(20)->withQueryString();
        });

        return view('categoriasfilhas.index', compact('categoriasfilhas', 'attribute'));
    }

    public function show(Request $request, $idOrSlug)
    {
        $page = $request->get('page', 1);
        $cacheKey = "cat_filha_show_{$idOrSlug}_p{$page}";

        // 1. Atributos globais (sempre carregar para o banner de fallback)
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        // 2. Busca da Categoria Filha e Produtos (Cacheado)
        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($idOrSlug) {
            // Busca por Slug OU ID
            $categoriasfilhas = CategoriasFilhas::with(['subcategory.category'])
                ->where('slug', $idOrSlug)
                ->orWhere('id', $idOrSlug)
                ->firstOrFail();

            // Paginação dos produtos com a marca e categoria carregadas para o card
            $products = $categoriasfilhas
                ->products()
                ->where('status', 1)
                ->where('product_role', 'P')
                ->where('stock', '>', 0)
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->with(['brand', 'category'])
                ->paginate(24)
                ->withQueryString();

            return [
                'categoriasfilhas' => $categoriasfilhas,
                'products' => $products,
            ];
        });

        // 3. Dados para o Filtro Completo (Sidebar)
        // Carregamos a árvore completa: Categoria > Subcategoria > Categoria Filha
        $categoriesTree = Cache::remember('filter_full_tree', now()->addHours(1), function () {
            return \App\Models\Category::where('status', 1)
                ->with(['subcategories.categoriasfilhas'])
                ->orderBy('name')
                ->get();
        });

        // Carregamos a lista de todas as marcas para o filtro lateral
        $brands = Cache::remember('filter_brands_list', now()->addHours(1), function () {
            return \App\Models\Brand::where('status', 1)->orderBy('name')->get();
        });

        return view('categoriasfilhas.show', [
            'categoriasfilhas' => $data['categoriasfilhas'],
            'products' => $data['products'],
            'attribute' => $attribute,
            'categories' => $categoriesTree, // Variável esperada pelo componente
            'brands' => $brands, // Variável esperada pelo componente
            // Auxiliares de contexto para o menu lateral saber o que destacar
            'currentChild' => $data['categoriasfilhas'],
            'currentSub' => $data['categoriasfilhas']->subcategory,
            'currentCat' => $data['categoriasfilhas']->subcategory->category ?? null,
        ]);
    }
}
