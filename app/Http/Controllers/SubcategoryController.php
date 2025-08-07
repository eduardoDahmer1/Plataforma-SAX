<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use Illuminate\Support\Facades\Storage;

class SubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = Subcategory::with('category')->orderBy('name')->paginate(12);

        return view('subcategories.index', compact('subcategories'));
    }

    public function show($id)
    {
        $subcategory = Subcategory::with('category')->findOrFail($id);

        return view('subcategories.show', compact('subcategory'));
    }
}
