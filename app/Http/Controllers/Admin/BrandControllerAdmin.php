<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $brands = Brand::where('status', 1) // Adicionado: Filtra apenas as ativas
            ->when(
                $search,
                fn($q) =>
                $q->where(function ($sub) use ($search) { // Agrupado para não quebrar o status
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
            'banner' => 'nullable|image|max:10240',
            'internal_banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only('name', 'slug');

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $this->convertToWebp($request->file('image'), 'logo');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        if ($request->hasFile('internal_banner') && $request->file('internal_banner')->isValid()) {
            $data['internal_banner'] = $this->convertToWebp($request->file('internal_banner'), 'internal_banner');
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
        // Aumenta o tempo para evitar o carregamento infinito (Error 504)
        set_time_limit(180);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug,' . $brand->id,
            'image' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
            'internal_banner' => 'nullable|image|max:10240',
        ]);

        $data = $request->only('name', 'slug');

        // Update Logo
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }
            $data['image'] = $this->convertToWebp($request->file('image'), 'logo');
        }

        // Update Banner Principal
        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            if ($brand->banner && Storage::disk('public')->exists($brand->banner)) {
                Storage::disk('public')->delete($brand->banner);
            }
            $data['banner'] = $this->convertToWebp($request->file('banner'), 'banner');
        }

        // Update Internal Banner
        if ($request->hasFile('internal_banner') && $request->file('internal_banner')->isValid()) {
            if ($brand->internal_banner && Storage::disk('public')->exists($brand->internal_banner)) {
                Storage::disk('public')->delete($brand->internal_banner);
            }
            $data['internal_banner'] = $this->convertToWebp($request->file('internal_banner'), 'internal_banner');
        }

        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('success', 'Marca atualizada com sucesso.');
    }

    public function destroy(Brand $brand)
    {
        $files = [$brand->image, $brand->banner, $brand->internal_banner];
        foreach ($files as $file) {
            if ($file && Storage::disk('public')->exists($file)) {
                Storage::disk('public')->delete($file);
            }
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Marca deletada com sucesso.');
    }

    private function convertToWebp($image, $type)
    {
        // Mapea el tipo a su carpeta destino; la conversión la hace el service.
        $directory = match ($type) {
            'banner' => 'brands/banner',
            'internal_banner' => 'brands/internal_banner',
            default => 'brands/logo',
        };

        return app(ImageConverterService::class)->toWebp($image, $directory);
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
        return redirect()->back()->with('success', 'Logo excluída.');
    }

    public function deleteBanner(Brand $brand)
    {
        if ($brand->banner && Storage::disk('public')->exists($brand->banner)) {
            Storage::disk('public')->delete($brand->banner);
        }
        $brand->banner = null;
        $brand->save();
        return redirect()->back()->with('success', 'Banner excluído.');
    }

    public function deleteInternalBanner(Brand $brand)
    {
        if ($brand->internal_banner && Storage::disk('public')->exists($brand->internal_banner)) {
            Storage::disk('public')->delete($brand->internal_banner);
        }
        $brand->internal_banner = null;
        $brand->save();
        return redirect()->back()->with('success', 'Banner interno excluído.');
    }

    public function uploadLogo(Request $request, Brand $brand)
    {
        $request->validate(['image' => 'required|image|max:10240']);
        if ($brand->image && Storage::disk('public')->exists($brand->image)) {
            Storage::disk('public')->delete($brand->image);
        }

        $path = $this->convertToWebp($request->file('image'), 'logo');
        $brand->image = $path;
        $brand->save();

        return response()->json(['success' => true, 'url' => Storage::url($path) . '?v=' . time()]);
    }

    public function uploadBanner(Request $request, Brand $brand)
    {
        $request->validate(['banner' => 'required|image|max:10240']);
        if ($brand->banner && Storage::disk('public')->exists($brand->banner)) {
            Storage::disk('public')->delete($brand->banner);
        }

        $path = $this->convertToWebp($request->file('banner'), 'banner');
        $brand->banner = $path;
        $brand->save();

        return response()->json(['success' => true, 'url' => Storage::url($path) . '?v=' . time()]);
    }

    public function uploadInternalBanner(Request $request, Brand $brand)
    {
        $request->validate(['internal_banner' => 'required|image|max:10240']);
        if ($brand->internal_banner && Storage::disk('public')->exists($brand->internal_banner)) {
            Storage::disk('public')->delete($brand->internal_banner);
        }

        $path = $this->convertToWebp($request->file('internal_banner'), 'internal_banner');
        $brand->internal_banner = $path;
        $brand->save();

        return response()->json(['success' => true, 'url' => Storage::url($path) . '?v=' . time()]);
    }
}
