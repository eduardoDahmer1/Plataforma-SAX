<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Storage;

class SubcategoryControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $query = Subcategory::with('category');
    
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhereHas('category', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }
    
        $subcategories = $query->paginate(18)->withQueryString();
    
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
            'photo' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only(['name', 'category_id']);

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        Subcategory::create($data);

        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategoria criada com sucesso.');
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
            'photo' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only(['name', 'category_id']);

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            if ($subcategory->photo) {
                $this->deleteFileIfExists($subcategory->photo);
            }
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            if ($subcategory->banner) {
                $this->deleteFileIfExists($subcategory->banner);
            }
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        $subcategory->update($data);

        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategoria atualizada com sucesso.');
    }

    public function show(Subcategory $subcategory)
    {
        return view('admin.subcategories.show', compact('subcategory'));
    }

    public function deletePhoto(Subcategory $subcategory)
    {
        if ($subcategory->photo) {
            $this->deleteFileIfExists($subcategory->photo);
            $subcategory->photo = null;
            $subcategory->save();
        }
        return back()->with('success', 'Foto excluída com sucesso.');
    }

    public function deleteBanner(Subcategory $subcategory)
    {
        if ($subcategory->banner) {
            $this->deleteFileIfExists($subcategory->banner);
            $subcategory->banner = null;
            $subcategory->save();
        }
        return back()->with('success', 'Banner excluído com sucesso.');
    }

    public function destroy(Subcategory $subcategory)
    {
        if ($subcategory->photo) {
            $this->deleteFileIfExists($subcategory->photo);
        }
        if ($subcategory->banner) {
            $this->deleteFileIfExists($subcategory->banner);
        }
        $subcategory->delete();

        return redirect()->route('admin.subcategories.index')->with('success', 'Subcategoria deletada com sucesso!');
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
        $extension = strtolower($image->getClientOriginalExtension());
    
        $directory = ($type === 'banner') ? 'subcategories/banner/' : 'subcategories/photo/';
        
        // Se já for webp ou avif, só salvar o arquivo sem conversão
        if (in_array($extension, ['webp', 'avif'])) {
            $finalName = uniqid() . '.' . $extension;
    
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
    
            Storage::disk('public')->putFileAs($directory, $image, $finalName);
            return "{$directory}{$finalName}";
        }
    
        // Conversão para webp
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
