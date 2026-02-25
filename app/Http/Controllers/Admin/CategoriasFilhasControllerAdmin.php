<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriasFilhas;
use App\Models\Subcategory;
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
        $data['slug'] = Str::slug($request->name);

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
        $data['slug'] = Str::slug($request->name);

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

    // ... (restante dos métodos auxiliares permanecem iguais)

    private function convertToWebp($file, $prefix)
    {
        $directory = ($prefix === 'banner') ? 'categorias-filhas/banner' : 'categorias-filhas/photo';
        $filename = $prefix . '_' . time() . '.webp';

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $tempPath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $imageResource = imagecreatefromjpeg($tempPath);
                break;
            case 'png':
                $imageResource = imagecreatefrompng($tempPath);
                break;
            case 'gif':
                $imageResource = imagecreatefromgif($tempPath);
                break;
            case 'webp':
            case 'avif':
                $finalName = $prefix . '_' . time() . '.' . $extension;
                Storage::disk('public')->putFileAs($directory, $file, $finalName);
                return "{$directory}/{$finalName}";
            default:
                throw new \Exception('Formato de imagem não suportado.');
        }

        if (!$imageResource) {
            throw new \Exception('Falha ao criar recurso de imagem.');
        }

        $fullPath = storage_path("app/public/{$directory}/{$filename}");
        imagewebp($imageResource, $fullPath, 85);
        imagedestroy($imageResource);

        return "{$directory}/{$filename}";
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
