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
        $search = $request->input('search', '');

        $cacheKey = "categories_index_{$page}_" . md5($search);

        $categories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            return Category::with(['subcategories.childcategories'])
                ->withCount('products')
                ->when($search, fn($q) =>
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                )
                ->paginate(20)
                ->withQueryString();
        });

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        $page = request()->get('page', 1);
        $cacheKey = "category_show_{$category->id}_{$page}";

        [$category, $products] = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($category) {
            $category = $category->load('subcategories.childcategories');
            $products = $category->products()->paginate(12)->withQueryString();
            return [$category, $products];
        });

        $cartItems = auth()->check()
            ? auth()->user()->cart()->pluck('quantity', 'product_id')->toArray()
            : [];

        return view('categories.show', compact('category', 'products', 'cartItems'));
    }
}
