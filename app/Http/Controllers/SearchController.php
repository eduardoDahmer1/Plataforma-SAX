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
        $perPage = $request->per_page ?? 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
    
        $cacheKey = 'search_' . md5(json_encode([
            'search' => $request->search,
            'brand' => $request->brand,
            'category' => $request->category,
            'subcategory' => $request->subcategory,
            'childcategory' => $request->childcategory,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'sort_by' => $request->sort_by,
            'per_page' => $perPage,
            'page' => $currentPage,
        ]));
    
        $paginated = Cache::remember($cacheKey, 600, function () use ($request, $perPage, $currentPage) {
            $query = Product::query()
                ->when($request->search, fn($q) => 
                    $q->where('external_name', 'like', "%{$request->search}%")
                      ->orWhere('sku', 'like', "%{$request->search}%")
                      ->orWhere('name', 'like', "%{$request->search}%")
                      ->orWhere('description', 'like', "%{$request->search}%")
                )
                ->when($request->brand, fn($q) => $q->where('brand_id', $request->brand))
                ->when($request->category, fn($q) => $q->where('category_id', $request->category))
                ->when($request->subcategory, fn($q) => $q->where('subcategory_id', $request->subcategory))
                ->when($request->childcategory, fn($q) => $q->where('childcategory_id', $request->childcategory))
                ->when($request->min_price, fn($q) => $q->where('price', '>=', $request->min_price))
                ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price));
    
            // Ordenação
            switch ($request->sort_by) {
                case 'latest': $query->orderBy('created_at','desc'); break;
                case 'oldest': $query->orderBy('created_at','asc'); break;
                case 'name_az': $query->orderBy('external_name','asc'); break;
                case 'name_za': $query->orderBy('external_name','desc'); break;
                case 'price_low': $query->orderBy('price','asc'); break;
                case 'price_high': $query->orderBy('price','desc'); break;
                case 'in_stock': $query->orderByRaw('stock>0 DESC')->orderBy('updated_at','desc'); break;
                default: $query->orderByRaw('(CASE WHEN photo IS NOT NULL AND photo != "" THEN 1 ELSE 0 END) DESC')
                               ->orderBy('updated_at','desc');
            }
    
            // Pega todos os produtos e filtra os que têm foto
            $filtered = $query->get()->filter(function($product) {
                return $product->photo && \Storage::disk('public')->exists($product->photo);
            });
    
            // Paginação manual
            $offset = ($currentPage - 1) * $perPage;
            $paginated = new LengthAwarePaginator(
                $filtered->slice($offset, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
    
            return $paginated;
        });
    
        // Filtros sidebar
        $brands = Cache::remember('search_brands', 600, fn() => Brand::orderBy('name')->get());
        $categories = Cache::remember('search_categories', 600, fn() => Category::orderBy('name')->get());
        $subcategories = Cache::remember('search_subcategories', 600, fn() => Subcategory::orderBy('name')->get());
        $childcategories = Cache::remember('search_childcategories', 600, fn() => Childcategory::orderBy('name')->get());
    
        // Carrinho
        $cartItems = [];
        if ($user = $request->user()) {
            $cartItems = Cart::where('user_id',$user->id)->pluck('quantity','product_id')->toArray();
        }
    
        return view('search.search', compact(
            'paginated','brands','categories','subcategories','childcategories','cartItems'
        ))->with('query',$request->search);
    }
    
}
