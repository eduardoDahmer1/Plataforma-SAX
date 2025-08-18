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

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

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
            ->orderByRaw('(CASE WHEN photo IS NOT NULL AND photo != "" THEN 1 ELSE 0 END) DESC')
            ->orderBy('updated_at', 'desc');

        $total = $productsQuery->count();
        $products = $productsQuery->skip(($currentPage - 1) * $perPage)->take($perPage)->get();

        $paginated = new LengthAwarePaginator(
            $products,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Se o usuário está logado, pega o carrinho
        $cartItems = [];
        if ($user = $request->user()) {
            $userCart = Cart::where('user_id', $user->id)->get();
            foreach ($userCart as $cart) {
                $cartItems[$cart->product_id] = $cart->quantity;
            }
        }

        $brands = Brand::whereHas('products', fn($q) => $q->when($request->category, fn($q2) => $q2->where('category_id', $request->category))
            ->when($request->subcategory, fn($q2) => $q2->where('subcategory_id', $request->subcategory))
            ->when($request->childcategory, fn($q2) => $q2->where('childcategory_id', $request->childcategory)))
            ->orderBy('name')->get();

        return view('home', [
            'paginated' => $paginated,
            'brands' => $brands,
            'categories' => Category::orderBy('name')->get(),
            'subcategories' => Subcategory::orderBy('name')->get(),
            'childcategories' => Childcategory::orderBy('name')->get(),
            'cartItems' => $cartItems,
        ]);
    }
}
