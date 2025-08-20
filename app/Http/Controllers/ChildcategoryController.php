<?php

namespace App\Http\Controllers;

use App\Models\Childcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChildcategoryController extends Controller
{
    public function index(Request $request)
    {
        $page   = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "childcategories_index_{$page}_" . md5($search);

        $childcategories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Childcategory::with('subcategory.category')->orderBy('name');

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->paginate(12)->withQueryString();
        });

        return view('Childcategory.index', compact('childcategories'));
    }

    public function show($slug)
    {
        $cacheKey = "childcategory_show_{$slug}";

        $childcategory = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($slug) {
            return Childcategory::with(['subcategory.category', 'products'])->where('slug', $slug)->firstOrFail();
        });

        return view('Childcategory.show', compact('childcategory'));
    }
}
