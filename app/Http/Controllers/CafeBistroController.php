<?php

namespace App\Http\Controllers;

use App\Models\CafeBistro;
use Illuminate\Support\Facades\Cache;

class CafeBistroController extends Controller
{
    public function index()
    {
        return $this->show('pedro-juan-caballero');
    }

    public function show(string $location)
    {
        $cafeBistro = Cache::remember("cafe_bistro_data_{$location}", 28800, function () use ($location) {
            return CafeBistro::with('translations')
                ->where('slug', $location)
                ->where('is_active', true)
                ->firstOrFail();
        });

        $cafeBistroLocations = Cache::remember('cafe_bistro_locations', 28800, fn () =>
            CafeBistro::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->get(['id', 'name', 'slug'])
        );

        return view('cafe_bistro.index', compact('cafeBistro', 'cafeBistroLocations'));
    }
}
