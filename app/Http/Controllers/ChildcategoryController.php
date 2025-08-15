<?php

namespace App\Http\Controllers;

use App\Models\Childcategory;
use Illuminate\Http\Request;

class ChildcategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Childcategory::with('subcategory.category');

        // Filtro por busca
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('slug', 'like', '%' . $request->search . '%');
            });
        }

        $childcategories = $query->paginate(12)->withQueryString();

        return view('Childcategory.index', compact('childcategories'));
    }

    public function show($slug)
    {
        $childcategory = Childcategory::with('subcategory.category')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('Childcategory.show', compact('childcategory'));
    }
}
