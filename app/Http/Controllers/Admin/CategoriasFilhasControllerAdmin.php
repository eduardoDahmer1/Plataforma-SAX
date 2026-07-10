<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriasFilhas;
use App\Models\Subcategory;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class CategoriasFilhasControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $query = CategoriasFilhas::with('subcategory');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhereHas('subcategory', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        // Ajuste para o nome da rota correto no seu sistema (.index)
        $categoriasfilhas = $query->paginate(18)->withQueryString();

        return view('admin.categoriasfilhas.index', compact('categoriasfilhas'));
    }

    public function create()
    {
        $subcategories = Subcategory::all();
        return view('admin.categoriasfilhas.create', compact('subcategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subcategory_id' => 'required|exists:subcategories,id',
            'photo' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only(['name', 'subcategory_id']);
        $data['slug'] = $this->resolveUniqueSlug($request->name);

        if (!empty($request->subcategory_id)) {
            $subcategory = Subcategory::find($request->subcategory_id);
            $data['category_id'] = $subcategory->category_id ?? null;
        }

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        CategoriasFilhas::create($data);

        // Limpa cache de busca para refletir o novo item
        Cache::forget('search_sidebar_v3');
        Cache::forget('search_sidebar_v4');

        return redirect()->route('admin.categorias-filhas.index')->with('success', 'Criado com sucesso');
    }

    /**
     * CORREÇÃO: Removida a linha que causava o erro Undefined variable $id
     */
    public function edit(CategoriasFilhas $categorias_filha)
    {
        // O parâmetro deve bater com o nome na rota {categorias_filha}
        $categoriasfilhas = $categorias_filha;
        $subcategories = Subcategory::all();

        return view('admin.categoriasfilhas.edit', compact('categoriasfilhas', 'subcategories'));
    }

    public function update(Request $request, CategoriasFilhas $categorias_filha)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subcategory_id' => 'required|exists:subcategories,id',
            'photo' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only(['name', 'subcategory_id']);
        $data['slug'] = $this->resolveUniqueSlug($request->name, $categorias_filha->id);

        if (!empty($request->subcategory_id)) {
            $subcategory = Subcategory::find($request->subcategory_id);
            $data['category_id'] = $subcategory->category_id ?? null;
        }

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $this->deleteFileIfExists($categorias_filha->photo);
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $this->deleteFileIfExists($categorias_filha->banner);
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        $categorias_filha->update($data);

        // Limpa caches
        Cache::forget('search_sidebar_v3');
        Cache::forget('search_sidebar_v4');

        return redirect()->route('admin.categorias-filhas.index')->with('success', 'Atualizado com sucesso');
    }

    public function destroy(CategoriasFilhas $categorias_filha)
    {
        $this->deleteFileIfExists($categorias_filha->photo);
        $this->deleteFileIfExists($categorias_filha->banner);
        $categorias_filha->delete();

        return back()->with('success', 'Removido com sucesso');
    }

    public function uploadPhoto(Request $request, CategoriasFilhas $categorias_filha)
    {
        $request->validate(['photo' => 'required|image|max:10240']);
        $this->deleteFileIfExists($categorias_filha->photo);

        $path = $this->convertToWebp($request->file('photo'), 'photo');
        $categorias_filha->photo = $path;
        $categorias_filha->save();

        return response()->json(['success' => true, 'url' => Storage::url($path) . '?v=' . time()]);
    }

    public function uploadBanner(Request $request, CategoriasFilhas $categorias_filha)
    {
        $request->validate(['banner' => 'required|image|max:10240']);
        $this->deleteFileIfExists($categorias_filha->banner);

        $path = $this->convertToWebp($request->file('banner'), 'banner');
        $categorias_filha->banner = $path;
        $categorias_filha->save();

        return response()->json(['success' => true, 'url' => Storage::url($path) . '?v=' . time()]);
    }

    // ... (restante dos métodos auxiliares permanecem iguais)

    // Gera um slug único (globalmente, entre todas as categorias filhas) em vez de deixar colidir
    // quando o mesmo nome (ex: "Pantalones") é usado em subcategorias diferentes.
    private function resolveUniqueSlug(string $name, $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'categoria-filha';
        $slug = $base;
        $suffix = 2;

        while (CategoriasFilhas::where('slug', $slug)->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function convertToWebp($file, $prefix)
    {
        // Mapea el prefijo a su carpeta destino; la conversión la hace el service.
        $directory = ($prefix === 'banner') ? 'categorias-filhas/banner' : 'categorias-filhas/photo';

        return app(ImageConverterService::class)->toWebp($file, $directory, [
            'quality' => 85,
            'strict'  => true,
        ]);
    }

    private function deleteFileIfExists($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function show(CategoriasFilhas $categorias_filha)
    {
        // Carrega as relações para evitar o erro de "undefined" no breadcrumb da view
        $categorias_filha->load(['subcategory', 'subcategory.category']);

        // Definimos a variável com o nome exato que a View espera
        $categoriasfilhas = $categorias_filha;

        return view('admin.categoriasfilhas.show', compact('categoriasfilhas'));
    }
}
