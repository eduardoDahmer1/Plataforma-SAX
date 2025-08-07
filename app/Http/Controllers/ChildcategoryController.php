<?php

namespace App\Http\Controllers;

use App\Models\Childcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChildcategoryController extends Controller
{
    public function index()
    {
        $childcategories = Childcategory::with('subcategory.category')->paginate(12);
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
