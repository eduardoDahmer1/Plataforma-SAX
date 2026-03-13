<?php

namespace App\Http\Controllers;

use App\Models\Institucional;
use App\Models\Brand;
use Illuminate\Support\Facades\Cache;

class InstitucionalController extends Controller
{
    public function index()
    {
        // Cache por 1440 minutos (24 horas)
        $data = Cache::remember('institucional_page_data', 1440, function () {
            return [
                'institucional' => Institucional::first() ?: new Institucional(),
                'brands' => Brand::whereNotNull('image')
                    ->where('status', 1)
                    ->get()
            ];
        });

        return view('institucional.index', [
            'institucional' => $data['institucional'],
            'brands' => $data['brands']
        ]);
    }
}