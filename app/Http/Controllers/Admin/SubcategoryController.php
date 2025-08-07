<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Childcategory;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Storage;

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
            'photo' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only(['name', 'subcategory_id']);

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        Childcategory::create($data);

        return redirect()->route('admin.childcategories.index')->with('success', 'Sub-subcategoria criada com sucesso.');
    }

    public function edit(Childcategory $childcategory)
    {
        $subcategories = Subcategory::all();
        return view('admin.childcategories.edit', compact('childcategory', 'subcategories'));
    }

    public function update(Request $request, Childcategory $childcategory)
    {
        $data = $request->only(['name', 'subcategory_id']);

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            if ($childcategory->photo) {
                $this->deleteFileIfExists($childcategory->photo);
            }
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            if ($childcategory->banner) {
                $this->deleteFileIfExists($childcategory->banner);
            }
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        $childcategory->update($data);

        return redirect()->route('admin.childcategories.index')->with('success', 'Sub-subcategoria atualizada com sucesso.');
    }

    public function show(Childcategory $childcategory)
    {
        return view('admin.childcategories.show', compact('childcategory'));
    }

    public function deletePhoto(Childcategory $childcategory)
    {
        if ($childcategory->photo) {
            $this->deleteFileIfExists($childcategory->photo);
            $childcategory->photo = null;
            $childcategory->save();
        }
        return back()->with('success', 'Foto excluída com sucesso.');
    }

    public function deleteBanner(Childcategory $childcategory)
    {
        if ($childcategory->banner) {
            $this->deleteFileIfExists($childcategory->banner);
            $childcategory->banner = null;
            $childcategory->save();
        }
        return back()->with('success', 'Banner excluído com sucesso.');
    }

    public function destroy($id)
    {
        $childcategory = Childcategory::findOrFail($id);

        if ($childcategory->photo) {
            $this->deleteFileIfExists($childcategory->photo);
        }

        if ($childcategory->banner) {
            $this->deleteFileIfExists($childcategory->banner);
        }

        $childcategory->delete();

        return redirect()->route('admin.childcategories.index')->with('success', 'Sub-subcategoria deletada com sucesso!');
    }

    private function deleteFileIfExists($filePath)
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
    }

    private function convertToWebp($image, $type)
    {
        $tempPath = $image->getRealPath();
        $imageResource = null;
        $extension = strtolower($image->getClientOriginalExtension());

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
            default:
                throw new \Exception('Formato de imagem não suportado.');
        }

        if (!$imageResource) {
            throw new \Exception('Falha ao criar recurso de imagem.');
        }

        $directory = ($type === 'banner') ? 'childcategories/banner/' : 'childcategories/photo/';
        $filename = uniqid() . '.webp';
        $fullPath = storage_path("app/public/{$directory}{$filename}");

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        imagewebp($imageResource, $fullPath, 45);
        imagedestroy($imageResource);

        return "{$directory}{$filename}";
    }
}
