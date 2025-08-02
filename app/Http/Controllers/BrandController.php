<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        // Obtém o termo de busca
        $search = $request->input('search');

        // Verifica se existe busca e aplica filtro no nome e id
        $brands = Cache::remember('brands.page.' . request('page', 1) . '.search.' . $search, 3600, function () use ($search) {
            if ($search) {
                // Busca por nome ou ID
                return Brand::where('name', 'like', "%{$search}%")
                            ->orWhere('id', $search)
                            ->orderBy('name')
                            ->paginate(20);
            } else {
                // Se não houver busca, retorna todas as marcas ordenadas pelo nome
                return Brand::orderBy('name')->paginate(20);
            }
        });

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(StoreBrandRequest $request)
    {
        $data = $request->only('name', 'slug');
    
        $request->validate([
            'image' => 'nullable|image|max:2048',
        ]);
    
        if ($request->hasFile('image')) {
            // Salva a imagem na pasta 'brands' dentro do disco 'public'
            $path = $request->file('image')->store('brands', 'public');
            $data['image'] = $path;
        }
    
        Brand::create($data);
        $this->clearBrandsCache();
    
        return redirect()->route('admin.brands.index')->with('success', 'Marca criada com sucesso.');
    }

    public function show(Brand $brand)
    {
        return view('admin.brands.show', compact('brand'));
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $data = $request->only('name', 'slug');
    
        $request->validate([
            'image' => 'nullable|image|max:2048',
        ]);
    
        if ($request->hasFile('image')) {
            // Apaga a imagem antiga se existir
            if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                Storage::disk('public')->delete($brand->image);
            }
    
            $path = $request->file('image')->store('brands', 'public');
            $data['image'] = $path;
        }
    
        $brand->update($data);
        $this->clearBrandsCache();
    
        return redirect()->route('admin.brands.index')->with('success', 'Marca atualizada com sucesso.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        $this->clearBrandsCache();

        return redirect()->route('admin.brands.index')->with('success', 'Marca deletada com sucesso.');
    }

    // Frontend - listar marcas
    public function publicIndex()
    {
        $brands = Brand::orderBy('name')->paginate(12);
        return view('brands.index', compact('brands'));
    }

    // Frontend - detalhe da marca
    public function publicShow(Brand $brand)
    {
        return view('brands.show', compact('brand'));
    }

    private function clearBrandsCache()
    {
        Cache::forget('brands.all');
    }
}
