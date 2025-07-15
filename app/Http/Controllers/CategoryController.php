<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    public function index()
    {
        // Captura o termo de busca
        $search = request('search');

        // Utiliza o cache com o filtro de busca
        $categories = Cache::remember('categories.search.' . md5($search) . '.page.' . request('page', 1), 3600, function () use ($search) {
            $query = Category::orderBy('name'); // Ordena as categorias pelo nome

            // Aplica filtro de busca se houver
            if ($search) {
                $query->where('name', 'like', "%{$search}%")  // Busca por nome
                      ->orWhere('id', $search)               // Busca por ID
                      ->orWhere('slug', 'like', "%{$search}%"); // Busca por slug
            }

            return $query->paginate(20); // Pagina os resultados
        });

        return view('pages.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('pages.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        Category::create($request->only('name', 'slug'));
        $this->clearCategoriesCache();

        return redirect()->route('categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function show(Category $category)
    {
        return view('pages.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('pages.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->only('name', 'slug'));
        $this->clearCategoriesCache();

        return redirect()->route('categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        $this->clearCategoriesCache();

        return redirect()->route('categories.index')->with('success', 'Categoria deletada com sucesso.');
    }

    private function clearCategoriesCache()
    {
        Cache::forget('categories.all');
    }
}
