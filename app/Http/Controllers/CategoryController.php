<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query()->orderBy('name');

        // Filtro por busca
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
            ->orWhere('slug', 'like', '%' . $request->search . '%');
        }

        $categories = $query->paginate(12)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        $category->load('subcategories.childcategories'); // carrega tudo de uma vez
        return view('categories.show', compact('category'));
    }
}
