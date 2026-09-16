<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageConverterService;
use App\Services\StoreTaxonomyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CategoryControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        // BUSCA DIRETA DO BANCO (Sem Cache::remember)
        $categories = app(StoreTaxonomyService::class)->categories(Category::query())
            ->where('status', 1)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(18);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'photo' => 'nullable|image|max:10240',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $data['photo'] = $this->convertToWebp($request->file('photo'));
        }

        Category::create($data);
        $this->clearNavigationCaches();

        return redirect()->route('admin.categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function show(Category $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id); // pega o category pelo ID

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,'.$category->id,
            'photo' => 'nullable|image|max:10240',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            if ($category->photo && Storage::disk('public')->exists($category->photo)) {
                Storage::disk('public')->delete($category->photo);
            }
            $data['photo'] = $this->convertToWebp($request->file('photo'));
        }

        $category->update($data);
        $this->clearNavigationCaches();

        return redirect()->route('admin.categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Category $category)
    {
        if ($category->photo && Storage::disk('public')->exists($category->photo)) {
            Storage::disk('public')->delete($category->photo);
        }
        $category->delete();
        $this->clearNavigationCaches();

        return redirect()->route('admin.categories.index')->with('success', 'Categoria excluída com sucesso.');
    }

    public function uploadPhoto(Request $request, $id)
    {
        $request->validate(['photo' => 'required|image|max:10240']);
        $category = Category::findOrFail($id);

        if ($category->photo && Storage::disk('public')->exists($category->photo)) {
            Storage::disk('public')->delete($category->photo);
        }

        $path = $this->convertToWebp($request->file('photo'));
        $category->photo = $path;
        $category->save();
        $this->clearNavigationCaches();

        return response()->json(['success' => true, 'url' => Storage::url($path).'?v='.time()]);
    }

    private function convertToWebp($image)
    {
        $directory = 'categories/photo';

        return app(ImageConverterService::class)->toWebp($image, $directory, [
            'quality' => 85,
            'strict' => true,
        ]);
    }

    public function deletePhoto($id)
    {
        $category = Category::findOrFail($id);

        if ($category->photo && Storage::disk('public')->exists($category->photo)) {
            Storage::disk('public')->delete($category->photo);
            $category->photo = null;
            $category->save();
            $this->clearNavigationCaches();
        }

        return back()->with('success', 'Imagem removida com sucesso.');
    }

    private function clearNavigationCaches(): void
    {
        foreach ([
            'header_categories_tree',
            'header_main_categories',
            'all_categories_tree_active',
            'all_categories_tree_visible_catalog_v2',
            'filter_full_tree_active',
            'filter_full_tree_visible_catalog_v2',
            'categories_home_strip_random_15min',
            'categories_all',
        ] as $key) {
            Cache::forget($key);
        }
    }
}
