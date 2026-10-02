<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Bridal;
use App\Models\CafeBistro;
use App\Models\HomeBanner;
use App\Models\Institucional;
use App\Models\Palace;
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

        // Cada card do ecossistema reutiliza a capa oficial da própria página.
        // Assim, qualquer troca feita no respectivo admin também aparece aqui.
        $homeBanner = HomeBanner::query()
            ->where('group', HomeBanner::GROUP_MAIN)
            ->where('is_active', true)
            ->whereNotNull('image')
            ->where('image', '<>', '')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
        $legacyHomeBanner = Attribute::query()->value('banner1');
        $cafes = CafeBistro::query()
            ->where('is_active', true)
            ->whereIn('slug', ['pedro-juan-caballero', 'asuncion'])
            ->pluck('hero_imagen', 'slug');

        $experienceBanners = [
            'department_store' => $homeBanner?->image_url
                ?: ($legacyHomeBanner ? asset('storage/uploads/'.ltrim($legacyHomeBanner, '/')) : null),
            'palace' => ($path = Palace::query()->value('hero_imagem')) ? asset('storage/'.$path) : null,
            'bridal' => ($path = Bridal::query()->value('hero_image')) ? asset('storage/'.$path) : null,
            'cafe_pjc' => $cafes->get('pedro-juan-caballero')
                ? asset('storage/'.$cafes->get('pedro-juan-caballero')) : null,
            'cafe_asuncion' => $cafes->get('asuncion')
                ? asset('storage/'.$cafes->get('asuncion')) : null,
            'guide' => asset('images/contact-guide/sax-banner-1600x300.png'),
        ];

        return view('institucional.index', [
            'institucional' => $institucional,
            'brands' => $data['brands'],
            'sceneryPool' => $sceneryPool,
            'experienceBanners' => $experienceBanners,
        ]);
    }
}