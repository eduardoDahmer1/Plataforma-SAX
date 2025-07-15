<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Cache::remember('brands.page.' . request('page', 1), 3600, function () {
            return Brand::orderBy('name')->paginate(20);
        });
    
        return view('pages.brands.index', compact('brands'));
    }
    

    public function create()
    {
        return view('pages.brands.create');
    }

    public function store(StoreBrandRequest $request)
    {
        Brand::create($request->only('name', 'slug'));
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
        $brand->update($request->only('name', 'slug'));
        $this->clearBrandsCache();

        return redirect()->route('brands.index')->with('success', 'Marca atualizada com sucesso.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        $this->clearBrandsCache();

        return redirect()->route('brands.index')->with('success', 'Marca deletada com sucesso.');
    }

    private function clearBrandsCache()
    {
        Cache::forget('brands.all');
    }
}
