<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Subcategory::with('category')->orderBy('name');

        // Filtro por busca
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $subcategories = $query->paginate(12)->withQueryString();

        return view('subcategories.index', compact('subcategories'));
    }

    public function show($slug)
    {
        $subcategory = Subcategory::with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('subcategories.show', compact('subcategory'));
    }
}
