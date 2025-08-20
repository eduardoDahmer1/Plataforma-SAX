<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        $categories = Category::with(['subcategories.childcategories'])
            ->withCount('products')
            ->when($search, fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
            )
            ->paginate(12)
            ->withQueryString();
    
        return view('categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        $cacheKey = "category_show_{$category->id}_" . request('page', 1);
    
        [$category, $products] = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($category) {
            $category = $category->load('subcategories.childcategories');
            $products = $category->products()->paginate(12)->withQueryString();
            return [$category, $products];
        });
    
        $cartItems = auth()->check() ? auth()->user()->cart()->pluck('quantity', 'product_id')->toArray() : [];
    
        return view('categories.show', compact('category', 'products', 'cartItems'));
    }
}
