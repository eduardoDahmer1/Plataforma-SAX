<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $productsQuery = Product::query()
            // Busca por texto
            ->when($request->search, function($q) use ($request) {
                $q->where('external_name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            })
            // Filtro por marca
            ->when($request->brand, function($q) use ($request) {
                $q->where('brand_id', $request->brand);
            })
            // Filtro por categoria
            ->when($request->category, function($q) use ($request) {
                $q->where('category_id', $request->category);
            })
            // Filtro por subcategoria
            ->when($request->subcategory, function($q) use ($request) {
                $q->where('subcategory_id', $request->subcategory);
            })
            // Filtro por categoria filha
            ->when($request->childcategory, function($q) use ($request) {
                $q->where('childcategory_id', $request->childcategory);
            })
            // Prioriza produtos com foto
            ->orderByRaw('(CASE WHEN photo IS NOT NULL AND photo != "" THEN 1 ELSE 0 END) DESC')
            ->orderBy('updated_at', 'desc');

        $total = $productsQuery->count();

        $products = $productsQuery->skip(($currentPage - 1) * $perPage)
                                  ->take($perPage)
                                  ->get();

        $paginated = new LengthAwarePaginator(
            $products,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Pega apenas marcas que tenham produtos filtrados
        $brands = Brand::whereHas('products', function($q) use ($request) {
                $q->when($request->category, fn($q2) => $q2->where('category_id', $request->category))
                  ->when($request->subcategory, fn($q2) => $q2->where('subcategory_id', $request->subcategory))
                  ->when($request->childcategory, fn($q2) => $q2->where('childcategory_id', $request->childcategory));
            })
            ->orderBy('name')
            ->get();

        return view('home', [
            'paginated' => $paginated,
            'brands' => $brands,
            'categories' => Category::orderBy('name')->get(),
            'subcategories' => Subcategory::orderBy('name')->get(),
            'childcategories' => Childcategory::orderBy('name')->get(),
            'search' => $request->search,
            'selectedBrand' => $request->brand,
            'selectedCategory' => $request->category,
            'selectedSubcategory' => $request->subcategory,
            'selectedChildcategory' => $request->childcategory,
        ]);
    }
}
