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
                'institucional' => Institucional::with('translations')->first() ?: new Institucional(),
                'brands' => Brand::whereNotNull('image')
                    ->where('status', 1)
                    ->get()
            ];
        });

        $institucional = $data['institucional'];

        // Pool com todas as imagens de "cenário" disponíveis (banners + galeria + capa), sem repetição,
        // usado para distribuir fotos diferentes entre os fundos (parallax, stats, cta) em vez de repetir sempre a mesma.
        $topSliders = is_array($institucional->top_sliders) ? $institucional->top_sliders : (json_decode($institucional->top_sliders, true) ?: []);
        $galleryImages = is_array($institucional->gallery_images) ? $institucional->gallery_images : (json_decode($institucional->gallery_images, true) ?: []);

        $sceneryPool = collect($topSliders)
            ->merge($galleryImages)
            ->push($institucional->section_one_image)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $sceneryPool = sax_rotate_images($sceneryPool, 2);

        return view('institucional.index', [
            'institucional' => $institucional,
            'brands' => $data['brands'],
            'sceneryPool' => $sceneryPool,
        ]);
    }
}