<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AllCategoriesController extends Controller
{
    public function index()
    {
        // 1. Atributos globais para banners/configurações
        $attribute = Cache::remember('global_attributes', now()->addHours(24), function () {
            return DB::table('attributes')->first();
        });

        // 2. Carrega a árvore completa tratando a ausência da coluna 'status'
        $categories = Cache::remember('all_categories_tree_complete', now()->addMinutes(60), function () {
            return Category::where('status', 1) // Assume-se que Category tem 'status'
                ->with(['subcategories' => function($query) {
                    // Removido o 'status' pois a coluna não existe em subcategories segundo o erro SQL
                    $query->orderBy('name');
                }, 'subcategories.categoriasfilhas' => function($query) {
                    $query->orderBy('name');
                }])
                ->orderBy('name')
                ->get();
        });

        return view('todascategorias.index', compact('categories', 'attribute'));
    }
}