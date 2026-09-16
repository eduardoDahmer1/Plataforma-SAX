<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BrandControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $brands = Brand::where('status', 1) // Adicionado: Filtra apenas as ativas
            ->when(
                $search,
                fn ($q) => $q->where(function ($sub) use ($search) { // Agrupado para não quebrar o status
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('id', $search);
                })
            )
            ->orderBy('name')
            ->paginate(18);

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
            'home_carousel_image' => 'nullable|image|dimensions:width=1080,height=1350|max:10240',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $this->convertToWebp($request->file('image'));
        }

        if ($request->hasFile('home_carousel_image') && $request->file('home_carousel_image')->isValid()) {
            $data['home_carousel_image'] = $this->convertHomeCarouselImage($request->file('home_carousel_image'));
        }

        Brand::create($data);
        $this->clearHomeBrandsCache();

        return redirect()->route('admin.brands.index')->with('success', 'Marca criada com sucesso.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        // Aumenta o tempo para evitar o carregamento infinito (Error 504)
        set_time_limit(180);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug,'.$brand->id,
            'image' => 'nullable|image|max:10240',
            'home_carousel_image' => 'nullable|image|dimensions:width=1080,height=1350|max:10240',
        ]);

        $data = $request->only('name', 'slug');

        // Update Logo
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }
            $data['image'] = $this->convertToWebp($request->file('image'));
        }

        $brand->update($data);
        $this->clearHomeBrandsCache();

        return redirect()->route('admin.brands.index')->with('success', 'Marca atualizada com sucesso.');
    }

    public function destroy(Brand $brand)
    {
        $files = [$brand->image, $brand->home_carousel_image];
        foreach ($files as $file) {
            if ($file && Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
            }
        }

        $brand->delete();
        $this->clearHomeBrandsCache();

        return redirect()->route('admin.brands.index')->with('success', 'Marca deletada com sucesso.');
    }

    private function convertToWebp($image)
    {
        $directory = 'brands/logo';

        return app(ImageConverterService::class)->toWebp($image, $directory);
    }

    private function convertHomeCarouselImage($image)
    {
        return app(ImageConverterService::class)->toWebp($image, 'brands/home-carousel', [
            'quality' => 90,
            'strict' => true,
        ]);
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
        $this->clearHomeBrandsCache();

        return redirect()->back()->with('success', 'Logo excluída.');
    }

    public function uploadLogo(Request $request, Brand $brand)
    {
        $request->validate(['image' => 'required|image|max:10240']);
        if ($brand->image && Storage::disk('public')->exists($brand->image)) {
            Storage::disk('public')->delete($brand->image);
        }

        $path = $this->convertToWebp($request->file('image'));
        $brand->image = $path;
        $brand->save();
        $this->clearHomeBrandsCache();

        return response()->json(['success' => true, 'url' => Storage::url($path).'?v='.time()]);
    }

    public function deleteHomeCarouselImage(Brand $brand)
    {
        if ($brand->home_carousel_image && Storage::disk('public')->exists($brand->home_carousel_image)) {
            Storage::disk('public')->delete($brand->home_carousel_image);
        }

        $brand->home_carousel_image = null;
        $brand->save();
        $this->clearHomeBrandsCache();

        return redirect()->back()->with('success', 'Imagem do carrossel da home excluída.');
    }

    public function uploadHomeCarouselImage(Request $request, Brand $brand)
    {
        $request->validate([
            'home_carousel_image' => 'required|image|dimensions:width=1080,height=1350|max:10240',
        ]);

        if ($brand->home_carousel_image && Storage::disk('public')->exists($brand->home_carousel_image)) {
            Storage::disk('public')->delete($brand->home_carousel_image);
        }

        $path = $this->convertHomeCarouselImage($request->file('home_carousel_image'));
        $brand->home_carousel_image = $path;
        $brand->save();
        $this->clearHomeBrandsCache();

        return response()->json(['success' => true, 'url' => Storage::url($path).'?v='.time()]);
    }

    private function clearHomeBrandsCache(): void
    {
        Cache::forget('home_brands_visible_catalog_v5_carousel_15min');
    }
}
