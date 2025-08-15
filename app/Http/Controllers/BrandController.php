<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    // Lista marcas públicas com busca e paginação
    public function publicIndex(Request $request)
    {
        $query = Brand::orderBy('name');

        // Filtro por busca
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $brands = $query->paginate(12)->withQueryString();

        return view('brands.index', compact('brands'));
    }

    // Mostra marca específica pelo slug
    public function publicShow($slug)
    {
        $brand = Brand::where('slug', $slug)->firstOrFail();

        return view('brands.show', compact('brand'));
    }
}
