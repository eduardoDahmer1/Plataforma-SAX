<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        if ($search) {
            $categories = Category::where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orderBy('name')
                ->paginate(20);
        } else {
            $categories = Category::orderBy('name')->paginate(20);
        }
    
        return view('admin.categories.index', compact('categories'));
    }    

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->only('name', 'slug'));
        $this->clearCategoriesCache();

        return redirect()->route('admin.categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function show(Category $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    // Frontend - listar categorias
    public function publicIndex()
    {
        $categories = Category::orderBy('name')->paginate(12);
        return view('categories.index', compact('categories'));
    }

    // Frontend - detalhe da categoria
    public function publicShow(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->only('name', 'slug'));
        $this->clearCategoriesCache();

        return redirect()->route('admin.categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        $this->clearCategoriesCache();

        return redirect()->route('admin.categories.index')->with('success', 'Categoria deletada com sucesso.');

    }

    private function clearCategoriesCache()
    {
        Cache::forget('categories.all');
    }
}
