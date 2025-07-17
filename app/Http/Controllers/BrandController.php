<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use Illuminate\Http\Request;

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

        return view('pages.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('pages.brands.create');
    }

    public function store(StoreBrandRequest $request)
    {
        $data = $request->only('slug');
        $data['name'] = $data['slug'];  // Garante que name = slug
    
        Brand::create($data);
        $this->clearBrandsCache();
    
        return redirect()->route('brands.index')->with('success', 'Marca criada com sucesso.');
    }

    public function show(Brand $brand)
    {
        return view('pages.brands.show', compact('brand'));
    }

    public function edit(Brand $brand)
    {
        return view('pages.brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $data = $request->only('slug');
        $data['name'] = $data['slug'];  // Garante que name = slug
    
        $brand->update($data);
        $this->clearBrandsCache();
    
        return redirect()->route('brands.index')->with('success', 'Marca atualizada com sucesso.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        $this->clearBrandsCache();

        return redirect()->route('admin.brands.index')->with('success', 'Marca deletada com sucesso.');
    }

    private function clearBrandsCache()
    {
        Cache::forget('brands.all');
    }
}
