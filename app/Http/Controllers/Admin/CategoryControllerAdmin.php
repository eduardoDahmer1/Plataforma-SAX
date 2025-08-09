<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $categories = Category::when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(9);

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
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        Category::create($data);

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

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'photo' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            if ($category->photo && Storage::disk('public')->exists($category->photo)) {
                Storage::disk('public')->delete($category->photo);
            }
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            if ($category->banner && Storage::disk('public')->exists($category->banner)) {
                Storage::disk('public')->delete($category->banner);
            }
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Category $category)
    {
        if ($category->photo && Storage::disk('public')->exists($category->photo)) {
            Storage::disk('public')->delete($category->photo);
        }
        if ($category->banner && Storage::disk('public')->exists($category->banner)) {
            Storage::disk('public')->delete($category->banner);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Categoria excluída com sucesso.');
    }

    private function convertToWebp($image, $type)
    {
        $tempPath = $image->getRealPath();
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
            case 'webp':
            case 'avif':
                // Se já for WEBP, só salva direto
                $directory = ($type === 'banner') ? 'categories/banner/' : 'categories/photo/';
                $filename = uniqid() . '.webp';
                Storage::disk('public')->putFileAs($directory, $image, $filename);
                return "{$directory}{$filename}";
            default:
                throw new \Exception('Formato de imagem não suportado.');
        }
    
        if (!$imageResource) {
            throw new \Exception('Falha ao criar recurso de imagem.');
        }
    
        ob_start();
        imagewebp($imageResource, null, 45); // qualidade 45
        $webpData = ob_get_clean();
        imagedestroy($imageResource);
    
        $directory = ($type === 'banner') ? 'categories/banner/' : 'categories/photo/';
        $filename = uniqid() . '.webp';
    
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
    
        Storage::disk('public')->put("{$directory}{$filename}", $webpData);
    
        return "{$directory}{$filename}";
    }
    

    public function deletePhoto($id)
    {
        $category = Category::findOrFail($id);

        if ($category->photo && Storage::disk('public')->exists($category->photo)) {
            Storage::disk('public')->delete($category->photo);
            $category->photo = null;
            $category->save();
        }

        return back()->with('success', 'Imagem removida com sucesso.');

    }

    public function deleteBanner($id)
    {
        $category = Category::findOrFail($id);

        if ($category->banner && Storage::disk('public')->exists($category->banner)) {
            Storage::disk('public')->delete($category->banner);
            $category->banner = null;
            $category->save();
        }

        return back()->with('success', 'Imagem removida com sucesso.');
    }

}
