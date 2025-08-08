<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    // Listagem de produtos com cache e pesquisa (front)
    public function index(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $columns = ['id', 'sku', 'external_name', 'slug', 'price', 'stock', 'photo', 'brand_id', 'category_id', 'subcategory_id', 'childcategory_id'];

        $cacheKey = $search ? null : "products_page_{$page}";

        $products = $cacheKey
            ? Cache::remember($cacheKey, now()->addMinutes(5), function () use ($columns) {
                return Product::select($columns)->orderBy('id', 'desc')->paginate(10);
            })
            : Product::select($columns)
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('external_name', 'LIKE', "%{$search}%")
                          ->orWhere('sku', 'LIKE', "%{$search}%")
                          ->orWhere('slug', 'LIKE', "%{$search}%");
                    });
                })
                ->orderBy('id', 'desc')
                ->paginate(10);

        return view('produtos.index', compact('products'));
    }

    // Detalhes do produto
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $uploads = $product->uploads;

        return view('produtos.show', compact('product', 'uploads'));
    }
}
