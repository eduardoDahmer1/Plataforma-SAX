<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    public function index()
    {
        return response()->json(BlogCategory::all());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = BlogCategory::create($request->all());
        return response()->json($category);
    }

    public function update(Request $request, BlogCategory $category)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy(BlogCategory $category)
    {
        $category->delete();
        return response()->json(['success' => true]);
    }
}
