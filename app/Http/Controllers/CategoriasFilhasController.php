<?php

namespace App\Http\Controllers;

use App\Models\CategoriasFilhas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($idOrSlug) {
            $categoriasfilhas = CategoriasFilhas::with(['subcategory.category'])
                ->where('slug', $idOrSlug)
                ->orWhere('id', $idOrSlug)
                ->firstOrFail();

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

        $categoriesTree = Cache::remember('filter_full_tree_active', now()->addHours(1), fn() => $this->buildFilterCategoriesTree());

        $brands = Cache::remember('filter_brands_list_active', now()->addHours(1), fn() => $this->buildFilterBrandsList());

        return view('catalog.show', [
            'entity' => $data['categoriasfilhas'],
            'products' => $data['products'],
            'attribute' => $attribute,
            'categories' => $categoriesTree,
            'brands' => $brands,
            'currentCategory' => $data['categoriasfilhas']->subcategory->category ?? null,
            'currentSub' => $data['categoriasfilhas']->subcategory,
            'currentChild' => $data['categoriasfilhas'],
            'backUrl' => route('categorias-filhas.index'),
            'backLabel' => 'VOLVER A CATEGORIAS FILHAS',
            'breadcrumb' => [
                $data['categoriasfilhas']->subcategory->category->name ?? '',
                $data['categoriasfilhas']->subcategory->name ?? '',
            ],
            'emptyMessage' => 'No se encontraron productos en esta categoría.',
        ]);
    }
}
