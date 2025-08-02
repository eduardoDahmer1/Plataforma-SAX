<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subcategory;
use App\Models\Category;

class SubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = Subcategory::with('category')->paginate(10);
        return view('admin.subcategories.index', compact('subcategories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.subcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        Subcategory::create($request->only(['name', 'category_id']));

        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategoria criada com sucesso.');
    }

    public function show(Subcategory $subcategory)
    {
        // Se quiser mostrar detalhes de uma subcategoria, pode criar essa view ou usar modal na listagem
        return view('admin.subcategories.show', compact('subcategory'));
    }

    public function edit(Subcategory $subcategory)
    {
        $categories = Category::all();
        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subcategory->update($request->only(['name', 'category_id']));

        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategoria atualizada com sucesso.');
    }

    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();

        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategoria removida.');
    }
}
