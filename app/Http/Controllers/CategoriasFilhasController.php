<?php

namespace App\Http\Controllers;

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
        $page   = $request->get('page', 1);
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

        // 2. Busca da Categoria e Produtos
        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($idOrSlug) {
            // Busca por Slug OU ID para evitar o erro 404 da sua imagem
            $categoriasfilhas = CategoriasFilhas::with(['subcategory.category'])
                ->where('slug', $idOrSlug)
                ->orWhere('id', $idOrSlug)
                ->firstOrFail();

            // Paginação dos produtos com a marca carregada
            $products = $categoriasfilhas->products()
                ->where('status', 1) // Garante que só produtos ativos apareçam
                ->where('product_role', 'P')
                ->with('brand')
                ->paginate(24)
                ->withQueryString();

            return [
                'categoriasfilhas' => $categoriasfilhas,
                'products'         => $products
            ];
        });

        return view('categoriasfilhas.show', [
            'categoriasfilhas' => $data['categoriasfilhas'],
            'products'         => $data['products'],
            'attribute'        => $attribute
        ]);
    }
}