<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    // Home pública
    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Query de produtos
        $productsQuery = Product::query()
        ->when($search, function ($q) use ($search) {
            $q->where('external_name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        })
        // Prioriza quem tem photo ou gallery preenchidos
        ->orderByRaw('(CASE WHEN photo IS NOT NULL AND photo != "" THEN 1 WHEN gallery IS NOT NULL AND gallery != "" THEN 1 ELSE 0 END) DESC')
        // Depois ordena por atualização mais recente
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

        return view('home', ['items' => $paginated]);
    }
}
