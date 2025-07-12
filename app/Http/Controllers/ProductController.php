<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Busca com filtro opcional por nome, SKU ou slug
        $query = Product::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('external_name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%");
        }

        $products = $query->orderBy('id', 'desc')->paginate(10);

        return view('produtos.index', compact('products'));
    }
}
