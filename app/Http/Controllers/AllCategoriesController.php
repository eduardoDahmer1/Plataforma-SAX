<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AllCategoriesController extends Controller
{
    public function index()
    {
        $attribute = Cache::remember('global_attributes', now()->addHours(24),
            fn() => DB::table('attributes')->first()
        );

        $categories = Cache::remember('all_categories_tree_visible_catalog_v2', now()->addMinutes(60),
            fn() => $this->buildFilterCategoriesTree()
        );

        return view('todascategorias.index', compact('categories', 'attribute'));
    }
}
