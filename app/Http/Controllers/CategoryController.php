<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        // Exibe categorias no site público
        $categories = Category::orderBy('name')->paginate(12);

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        // Pode carregar produtos ou subcategorias junto se precisar
        $category->load('subcategories');

        return view('categories.show', compact('category'));
    }
}
