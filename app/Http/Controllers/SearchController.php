<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Cart;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // 🔹 Cache por busca + filtros
        $cacheKey = 'search_' . md5(json_encode([
            'search'       => $request->search,
            'brand'        => $request->brand,
            'category'     => $request->category,
            'subcategory'  => $request->subcategory,
            'childcategory'=> $request->childcategory,
            'min_price'    => $request->min_price,
            'max_price'    => $request->max_price,
            'page'         => $currentPage,
        ]));

        $paginated = Cache::remember($cacheKey, 600, function () use ($request, $perPage, $currentPage) {
            $productsQuery = Product::query()
                ->when($request->search, function($q) use ($request) {
                    $q->where('external_name', 'like', "%{$request->search}%")
                      ->orWhere('sku', 'like', "%{$request->search}%")
                      ->orWhere('name', 'like', "%{$request->search}%")
                      ->orWhere('description', 'like', "%{$request->search}%");
                })
                ->when($request->brand, fn($q) => $q->where('brand_id', $request->brand))
                ->when($request->category, fn($q) => $q->where('category_id', $request->category))
                ->when($request->subcategory, fn($q) => $q->where('subcategory_id', $request->subcategory))
                ->when($request->childcategory, fn($q) => $q->where('childcategory_id', $request->childcategory))
                ->when($request->min_price, fn($q) => $q->where('price', '>=', $request->min_price))
                ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price))
                ->orderByRaw('(CASE WHEN photo IS NOT NULL AND photo != "" THEN 1 ELSE 0 END) DESC')
                ->orderBy('updated_at', 'desc');

            $total = $productsQuery->count();
            $products = $productsQuery->skip(($currentPage - 1) * $perPage)->take($perPage)->get();

            return new LengthAwarePaginator(
                $products,
                $total,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        });

        // 🔹 Filtros
        $brandsKey = 'search_brands_' . md5(json_encode([
            'category'     => $request->category,
            'subcategory'  => $request->subcategory,
            'childcategory'=> $request->childcategory,
        ]));

        $brands = Cache::remember($brandsKey, 600, function () use ($request) {
            return Brand::whereHas('products', fn($q) =>
                $q->when($request->category, fn($q2) => $q2->where('category_id', $request->category))
                  ->when($request->subcategory, fn($q2) => $q2->where('subcategory_id', $request->subcategory))
                  ->when($request->childcategory, fn($q2) => $q2->where('childcategory_id', $request->childcategory))
            )->orderBy('name')->get();
        });

        $categories = Cache::remember('search_categories_all', 600, function () {
            return Category::selectRaw("id, COALESCE(NULLIF(name, ''), slug) as name")
                ->whereNotNull('slug')
                ->orderBy('name')
                ->get();
        });

        $subcategories = Cache::remember('search_subcategories_all', 600, function () {
            return Subcategory::selectRaw("id, COALESCE(NULLIF(name, ''), slug) as name")
                ->whereNotNull('slug')
                ->orderBy('name')
                ->get();
        });

        $childcategories = Cache::remember('search_childcategories_all', 600, function () {
            return Childcategory::selectRaw("id, COALESCE(NULLIF(name, ''), slug) as name")
                ->whereNotNull('slug')
                ->orderBy('name')
                ->get();
        });

        // 🔹 Carrinho
        $cartItems = [];
        if ($user = $request->user()) {
            $cartItems = Cart::where('user_id', $user->id)
                ->pluck('quantity', 'product_id')
                ->toArray();
        }

        return view('search.search', [
            'query'          => $request->search,
            'paginated'      => $paginated,
            'brands'         => $brands,
            'categories'     => $categories,
            'subcategories'  => $subcategories,
            'childcategories'=> $childcategories,
            'cartItems'      => $cartItems,
        ]);
    }
}
