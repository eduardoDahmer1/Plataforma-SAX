<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Childcategory;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ChildcategoryControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $query = Childcategory::with('subcategory');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhereHas('subcategory', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $childcategories = $query->paginate(18)->withQueryString();

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

        Childcategory::create($data);
        return redirect()->route('admin.childcategories.index')->with('success', 'Criado com sucesso');
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
            $this->deleteFileIfExists($childcategory->photo);
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $this->deleteFileIfExists($childcategory->banner);
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        $childcategory->update($data);
        return redirect()->route('admin.childcategories.index')->with('success', 'Atualizado com sucesso');
    }

    public function destroy(Childcategory $childcategory)
    {
        $this->deleteFileIfExists($childcategory->photo);
        $this->deleteFileIfExists($childcategory->banner);
        $childcategory->delete();
        return back()->with('success', 'Removido com sucesso');
    }

    public function deletePhoto(Childcategory $childcategory)
    {
        $this->deleteFileIfExists($childcategory->photo);
        $childcategory->update(['photo' => null]);
        return back();
    }

    public function deleteBanner(Childcategory $childcategory)
    {
        $this->deleteFileIfExists($childcategory->banner);
        $childcategory->update(['banner' => null]);
        return back();
    }

    private function convertToWebp($file, $prefix)
    {
        $directory = ($prefix === 'banner') ? 'childcategories/banner' : 'childcategories/photo';
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

    // Frontend show via slug ou ID
    public function show($identifier)
    {
        $cacheKey = "childcategory_show_{$identifier}";

        $childcategory = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($identifier) {
            return Childcategory::with(['subcategory', 'subcategory.category'])
                ->when(is_numeric($identifier), fn($q) => $q->where('id', $identifier))
                ->when(!is_numeric($identifier), fn($q) => $q->where('slug', $identifier))
                ->firstOrFail();
        });

        return view('admin.childcategories.show', compact('childcategory'));
    }
}
