<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $brands = Brand::when($search, fn($q) => 
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('id', $search)
        )->orderBy('name')->paginate(18);

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug',
            'image' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $this->convertToWebp($request->file('image'), 'logo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        Brand::create($data);

        return redirect()->route('admin.brands.index')->with('success', 'Marca criada com sucesso.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug,' . $brand->id,
            'image' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }
            $data['image'] = $this->convertToWebp($request->file('image'), 'logo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            if ($brand->banner && Storage::disk('public')->exists($brand->banner)) {
                Storage::disk('public')->delete($brand->banner);
            }
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('success', 'Marca atualizada com sucesso.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->image && Storage::disk('public')->exists($brand->image)) {
            Storage::disk('public')->delete($brand->image);
        }
        if ($brand->banner && Storage::disk('public')->exists($brand->banner)) {
            Storage::disk('public')->delete($brand->banner);
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Marca deletada com sucesso.');
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
                // Já é webp, só salva sem conversão
                $directory = ($type === 'banner') ? 'brands/banner/' : 'brands/logo/';
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
        imagewebp($imageResource, null, 45);
        $webpData = ob_get_clean();
        imagedestroy($imageResource);
    
        $directory = ($type === 'banner') ? 'brands/banner/' : 'brands/logo/';
        $filename = uniqid() . '.webp';
    
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
    
        Storage::disk('public')->put("{$directory}{$filename}", $webpData);
    
        return "{$directory}{$filename}";
    }
    
    public function show($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.show', compact('brand'));
    }

    public function deleteLogo(Brand $brand)
    {
        if ($brand->image && Storage::disk('public')->exists($brand->image)) {
            Storage::disk('public')->delete($brand->image);
        }
        $brand->image = null;
        $brand->save();
    
        return redirect()->route('admin.brands.edit', $brand->id)->with('success', 'Logo excluída com sucesso.');
    }
    
    public function deleteBanner(Brand $brand)
    {
        if ($brand->banner && Storage::disk('public')->exists($brand->banner)) {
            Storage::disk('public')->delete($brand->banner);
        }
        $brand->banner = null;
        $brand->save();
    
        return redirect()->route('admin.brands.edit', $brand->id)
            ->with('success', 'Banner excluído com sucesso.');
    }
    
}
