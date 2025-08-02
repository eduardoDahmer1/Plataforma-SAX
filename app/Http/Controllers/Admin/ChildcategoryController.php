<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Childcategory;
use App\Models\Subcategory;
use Illuminate\Support\Str;

class ChildcategoryController extends Controller
{
    public function index()
    {
        $childcategories = Childcategory::with('subcategory')->paginate(10);
        return view('admin.childcategories.index', compact('childcategories'));
    }

    public function create()
    {
        $subcategories = Subcategory::all();
        return view('admin.childcategories.create', compact('subcategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subcategory_id' => 'required|exists:subcategories,id',
        ]);
    
        $data = $request->only(['name', 'subcategory_id']);
        $data['slug'] = Str::slug($data['name']); // gera o slug a partir do nome
    
        Childcategory::create($data);
    
        return redirect()->route('admin.childcategories.index')->with('success', 'Childcategoria criada com sucesso.');
    }

    public function show(Childcategory $childcategory)
    {
        return view('admin.childcategories.show', compact('childcategory'));
    }

    public function edit(Childcategory $childcategory)
    {
        $subcategories = Subcategory::all();
        return view('admin.childcategories.edit', compact('childcategory', 'subcategories'));
    }

    public function update(Request $request, Childcategory $childcategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subcategory_id' => 'required|exists:subcategories,id',
        ]);
    
        $data = $request->only(['name', 'subcategory_id']);
        $data['slug'] = Str::slug($data['name']); // atualiza o slug
    
        $childcategory->update($data);
    
        return redirect()->route('admin.childcategories.index')->with('success', 'Childcategoria atualizada com sucesso.');
    }

    public function destroy(Childcategory $childcategory)
    {
        $childcategory->delete();

        return redirect()->route('admin.childcategories.index')->with('success', 'Childcategoria removida.');
    }
}
