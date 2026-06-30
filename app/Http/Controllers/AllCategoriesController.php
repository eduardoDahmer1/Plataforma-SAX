<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AllCategoriesController extends Controller
{
    public function index()
    {
        $attribute = Cache::remember('global_attributes', now()->addHours(24),
            fn() => DB::table('attributes')->first()
        );

        $categories = Cache::remember('all_categories_tree_complete', now()->addMinutes(60),
            fn() => Category::where('status', 1)
                ->with([
                    'subcategories'                  => fn($q) => $q->orderBy('name'),
                    'subcategories.categoriasfilhas' => fn($q) => $q->orderBy('name'),
                ])
                ->orderBy('name')
                ->get()
        );

        return view('todascategorias.index', compact('categories', 'attribute'));
    }
}
