<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $page   = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "categories_index_{$page}_".md5($search);

        $categories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Category::orderBy('name');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            return $query->paginate(12)->withQueryString();
        });

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        $cacheKey = "category_show_{$category->id}";

        $category = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($category) {
            return $category->load('subcategories.childcategories');
        });

        return view('categories.show', compact('category'));
    }
}
