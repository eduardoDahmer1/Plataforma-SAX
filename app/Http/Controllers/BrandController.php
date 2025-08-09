<?php

namespace App\Http\Controllers;

use App\Models\Brand;

class BrandController extends Controller
{
    public function publicIndex()
    {
        $brands = Brand::orderBy('name')->paginate(12);
        return view('brands.index', compact('brands'));
    }

    public function publicShow(Brand $brand)
    {
        return view('brands.show', compact('brand'));
    }
}
