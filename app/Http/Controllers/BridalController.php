<?php

namespace App\Http\Controllers;

use App\Models\Bridal;
use App\Models\Brand;
use App\Services\VisibleCatalogProductsService;
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
        $brands = Cache::remember('bridal_active_brands', now()->addMinutes(30), fn () =>
            Brand::where('status', 1)->whereIn('id', $idbrands)->get()
        );

        // obtener los productos relacionados con las marcas específicas, asegurando que tengan una foto válida
        $bridalProducts = Cache::remember('bridal_visible_catalog_v2_products', now()->addMinutes(10), fn () =>
            VisibleCatalogProductsService::builder()
                ->with(['brand', 'translations'])
                ->whereIn('products.brand_id', $idbrands)
                ->latest()
                ->take(10)
                ->get()
        );

        return view('bridal.index', compact('bridal', 'brands', 'bridalProducts'));
    }
}
