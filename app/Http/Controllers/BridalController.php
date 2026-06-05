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
        // Cache por 28800 minutos (20 dias) trazendo as traduções polimórficas associadas ao Bridal
        $bridal = Cache::remember('bridal_data', 28800, function () {
            return Bridal::with('translations')->first() ?: new Bridal();
        });

        // IDs de las marcas específicas para Bridal(brand ticker)
        $idbrands = [641, 1444, 1237, 1236, 664, 951, 610];
        $brands   = Brand::where('status', 1)->whereIn('id', $idbrands)->get();

        // obtener los productos relacionados con las marcas específicas, asegurando que tengan una foto válida
        $bridalProducts = Product::with('brand')
            ->whereIn('brand_id', $idbrands)
            ->where('status', 1)
            ->where('product_role', 'P')
            ->where('stock', '>', 0)
            ->latest()
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->take(10)
            ->get();

        return view('bridal.index', compact('bridal', 'brands', 'bridalProducts'));
    }
}