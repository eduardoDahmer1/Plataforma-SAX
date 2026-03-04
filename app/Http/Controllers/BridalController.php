<?php

namespace App\Http\Controllers;

use App\Models\Bridal;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BridalController extends Controller
{
    public function index()
    {
        $bridal = Cache::remember('bridal_data', 28800, fn() => Bridal::first()) ?? new Bridal();

        $idbrands = [641, 1444, 1237, 1236, 664, 951, 610];
        $brands   = Brand::where('status', 1)->whereIn('id', $idbrands)->get();
        $bridalProducts = Product::with('brand')
            ->whereIn('brand_id', $idbrands)
            ->latest()
            ->take(10)
            ->get(); 
        return view('bridal.index', compact('bridal', 'brands', 'bridalProducts'));
    }
}
