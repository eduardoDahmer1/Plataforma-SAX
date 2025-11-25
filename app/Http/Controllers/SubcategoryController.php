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

    public function show($slug)
    {
        $cacheKey = "subcategory_show_{$slug}";
    
        $subcategory = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($slug) {
            return Subcategory::with(['category', 'childcategories'])
                ->where('slug', $slug)
                ->firstOrFail();
        });
    
        return view('subcategories.show', compact('subcategory'));
    }    
}
