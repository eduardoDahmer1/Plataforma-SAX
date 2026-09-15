<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bridal;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BridalAdminController extends Controller
{
    /**
     * Dashboard resumen del contenido Bridal.
     */
    public function index()
    {
        $bridal = Bridal::with('translations')->first() ?? Bridal::create(['hero_title' => 'SAX Bridal']);

        return view('admin.bridal.index', compact('bridal'));
    }

    /**
     * Formulario de edición.
     */
    public function edit($id)
    {
        $bridal = Bridal::with('translations')->findOrFail($id);

        return view('admin.bridal.edit', compact('bridal'));
    }

    /**
     * Procesa la actualización de todos los campos, imágenes y traducciones.
     */
    public function update(Request $request, $id)
    {
        $bridal = Bridal::findOrFail($id);

        $data = $request->validate([
            'translate' => 'required|array',
            'translate.pt-br' => 'required|array',
            'translate.es' => 'required|array',
            'translate.en' => 'required|array',
            'translate.*.bridal_hero_title' => 'nullable|string|max:255',
            'translate.*.bridal_hero_subtitle' => 'nullable|string|max:255',
            'translate.*.bridal_hero_description' => 'nullable|string',
            'translate.*.bridal_services_label' => 'nullable|string|max:255',
            'translate.*.bridal_services_title' => 'nullable|string|max:255',
            'translate.*.bridal_services_cta_text' => 'nullable|string|max:255',
            'translate.*.bridal_testimonials_label' => 'nullable|string|max:255',
            'translate.*.bridal_testimonials_title' => 'nullable|string|max:255',
            'translate.*.bridal_meta_title' => 'nullable|string|max:255',
            'translate.*.bridal_meta_description' => 'nullable|string',
            'translate.*.bridal_services' => 'nullable|array|max:4',
            'translate.*.bridal_services.*.title' => 'nullable|string|max:255',
            'translate.*.bridal_services.*.description' => 'nullable|string',
            'translate.*.bridal_promos' => 'nullable|array|max:3',
            'translate.*.bridal_promos.*.title' => 'nullable|string|max:255',
            'translate.*.bridal_promos.*.subtitle' => 'nullable|string|max:255',
            'translate.*.bridal_promos.*.button' => 'nullable|string|max:255',
            'translate.*.bridal_testimonials' => 'nullable|array|max:4',
            'translate.*.bridal_testimonials.*.quote' => 'nullable|string|max:200',
            'translate.*.bridal_testimonials.*.author' => 'nullable|string|max:255',
            'translate.*.bridal_testimonials.*.ubicacion' => 'nullable|string|max:255',
            'translate.*.bridal_locations' => 'nullable|array',
            'translate.*.bridal_locations.*.name' => 'nullable|string|max:255',
            'translate.*.bridal_locations.*.address' => 'nullable|string|max:255',

            // Básicos
            'title' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',

            'services_cta_link' => 'nullable|string|max:255',

            // Palace Banner
            'palace_subtitle' => 'nullable|string|max:255',
            'palace_title' => 'nullable|string|max:255',
            'palace_description' => 'nullable|string',
            'palace_link' => 'nullable|string|max:255',

            // Instagram CTA
            'social_instagram' => 'nullable|string|max:255',

            // Imágenes simples
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:8192',
            'palace_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',

            // Locations (N dinámico, con imagen)
            'locations_items' => 'nullable|array',
            'locations_items.*.phone' => 'nullable|string|max:255',
            'locations_items.*.image_path' => 'nullable|string',
            'locations_items.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',

            // Services (4 bloques fijos, con imagen)
            'services_items' => 'nullable|array|max:4',
            'services_items.*.image_path' => 'nullable|string',
            'services_items.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',

            // Promos (3 bloques fijos)
            'promos_items' => 'nullable|array|max:3',
            'promos_items.*.link' => 'nullable|string|max:255',
            'promos_items.*.image_path' => 'nullable|string',
            'promos_items.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',

            // Brands (N dinámico)
            'brands_items' => 'nullable|array',
            'brands_items.*.nombre' => 'nullable|string|max:255',
            'brands_items.*.logo_path' => 'nullable|string',
            'brands_items.*.logo_imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:2048',

            // Testimonials (4 bloques fijos)
            'testimonials_items' => 'nullable|array|max:4',
            'testimonials_items.*.foto_path' => 'nullable|string',
            'testimonials_items.*.foto' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:2048',
        ]);

        $translationsInput = $data['translate'];

        // 1. Procesar imágenes individuales (con conversión WebP)
        $fileFields = ['hero_image', 'palace_image'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($bridal->$field) {
                    Storage::disk('public')->delete($bridal->$field);
                }
                $data[$field] = $this->convertToWebp($request->file($field), 'bridal');
            }
        }

        // Imagens, links e telefones são estruturais e compartilhados entre idiomas.
        $serviceImages = $promoAssets = $testimonialPhotos = $locationAssets = [];
        foreach ($request->input('services_items', []) as $index => $item) {
            $serviceImages[$index] = $request->hasFile("services_items.$index.image")
                ? $this->convertToWebp($request->file("services_items.$index.image"), 'bridal/services')
                : ($item['image_path'] ?? null);
        }
        foreach ($request->input('promos_items', []) as $index => $item) {
            $promoAssets[$index] = [
                'image' => $request->hasFile("promos_items.$index.image")
                    ? $this->convertToWebp($request->file("promos_items.$index.image"), 'bridal/promos')
                    : ($item['image_path'] ?? null),
                'link' => $item['link'] ?? '',
            ];
        }
        foreach ($request->input('testimonials_items', []) as $index => $item) {
            $testimonialPhotos[$index] = $request->hasFile("testimonials_items.$index.foto")
                ? $this->convertToWebp($request->file("testimonials_items.$index.foto"), 'bridal/testimonials')
                : ($item['foto_path'] ?? null);
        }
        foreach ($request->input('locations_items', []) as $index => $item) {
            $phone = preg_replace('/\D+/', '', (string) ($item['phone'] ?? ''));
            $locationAssets[$index] = [
                'image' => $request->hasFile("locations_items.$index.image")
                    ? $this->convertToWebp($request->file("locations_items.$index.image"), 'bridal/locations')
                    : ($item['image_path'] ?? null),
                'whatsapp_url' => $phone !== '' ? 'https://wa.me/'.$phone : null,
            ];
        }

        // 4. Construir JSON de brands (N dinámico, con logo opcional)
        $brands = [];
        if ($request->has('brands_items')) {
            foreach ($request->input('brands_items', []) as $index => $item) {
                $logoPath = $item['logo_path'] ?? null;
                if ($request->hasFile("brands_items.$index.logo_imagen")) {
                    $logoPath = $this->convertToWebp(
                        $request->file("brands_items.$index.logo_imagen"),
                        'bridal/brands'
                    );
                }
                if (! empty($item['nombre'])) {
                    $brands[] = [
                        'nombre' => $item['nombre'],
                        'logo_imagen' => $logoPath,
                    ];
                }
            }
        }

        $localizedCollections = [];
        foreach (['pt-br', 'es', 'en'] as $locale) {
            $localeInput = $translationsInput[$locale] ?? [];

            $services = [];
            foreach (($localeInput['bridal_services'] ?? []) as $index => $item) {
                $image = $serviceImages[$index] ?? null;
                if (filled($item['title'] ?? null) || filled($item['description'] ?? null) || $image) {
                    $services[] = ['image' => $image, 'title' => $item['title'] ?? '', 'description' => $item['description'] ?? ''];
                }
            }

            $promos = [];
            foreach (($localeInput['bridal_promos'] ?? []) as $index => $item) {
                $asset = $promoAssets[$index] ?? ['image' => null, 'link' => ''];
                if (filled($item['title'] ?? null) || filled($item['subtitle'] ?? null) || $asset['image']) {
                    $promos[] = [
                        'image' => $asset['image'],
                        'title' => $item['title'] ?? '',
                        'subtitle' => $item['subtitle'] ?? '',
                        'button' => $item['button'] ?? '',
                        'link' => $asset['link'],
                    ];
                }
            }

            $testimonials = [];
            foreach (($localeInput['bridal_testimonials'] ?? []) as $index => $item) {
                $photo = $testimonialPhotos[$index] ?? null;
                if (filled($item['quote'] ?? null) || filled($item['author'] ?? null) || $photo) {
                    $testimonials[] = [
                        'quote' => $item['quote'] ?? '',
                        'author' => $item['author'] ?? '',
                        'foto' => $photo,
                        'ubicacion' => $item['ubicacion'] ?? '',
                    ];
                }
            }

            $locations = [];
            foreach (($localeInput['bridal_locations'] ?? []) as $index => $item) {
                $asset = $locationAssets[$index] ?? ['image' => null, 'whatsapp_url' => null];
                if (filled($item['name'] ?? null) || filled($item['address'] ?? null) || $asset['image']) {
                    $locations[] = [
                        'name' => $item['name'] ?? '',
                        'address' => $item['address'] ?? '',
                        'whatsapp_url' => $asset['whatsapp_url'],
                        'image' => $asset['image'],
                    ];
                }
            }

            $localizedCollections[$locale] = compact('services', 'promos', 'testimonials', 'locations');
        }

        $portuguese = $translationsInput['pt-br'];
        $portugueseCollections = $localizedCollections['pt-br'];

        // A tabela principal continua como fallback em português para registros antigos.
        $bridal->update([
            'title' => $data['title'] ?? $bridal->title,
            'is_active' => $request->boolean('is_active'),
            'services_cta_link' => $data['services_cta_link'] ?? $bridal->services_cta_link,
            'palace_link' => $data['palace_link'] ?? $bridal->palace_link,
            'social_instagram' => $data['social_instagram'] ?? $bridal->social_instagram,
            'hero_image' => $data['hero_image'] ?? $bridal->hero_image,
            'palace_image' => $data['palace_image'] ?? $bridal->palace_image,
            'brands' => ! empty($brands) ? $brands : $bridal->brands,
            'meta_title' => $portuguese['bridal_meta_title'] ?? null,
            'meta_description' => $portuguese['bridal_meta_description'] ?? null,
            'hero_title' => $portuguese['bridal_hero_title'] ?? null,
            'hero_subtitle' => $portuguese['bridal_hero_subtitle'] ?? null,
            'hero_description' => $portuguese['bridal_hero_description'] ?? null,
            'services_label' => $portuguese['bridal_services_label'] ?? null,
            'services_title' => $portuguese['bridal_services_title'] ?? null,
            'services_cta_text' => $portuguese['bridal_services_cta_text'] ?? null,
            'testimonials_label' => $portuguese['bridal_testimonials_label'] ?? null,
            'testimonials_title' => $portuguese['bridal_testimonials_title'] ?? null,
            'services' => $portugueseCollections['services'],
            'promos' => $portugueseCollections['promos'],
            'testimonials' => $portugueseCollections['testimonials'],
            'locations' => $portugueseCollections['locations'],
        ]);

        // Um único envio atualiza PT, ES e EN sem troca global de idioma.
        foreach (['pt-br', 'es', 'en'] as $locale) {
            $localeInput = $translationsInput[$locale];
            $collections = $localizedCollections[$locale];
            $bridal->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'bridal_title' => $data['title'] ?? $bridal->title,
                    'bridal_meta_title' => $localeInput['bridal_meta_title'] ?? null,
                    'bridal_meta_description' => $localeInput['bridal_meta_description'] ?? null,
                    'bridal_hero_title' => $localeInput['bridal_hero_title'] ?? null,
                    'bridal_hero_subtitle' => $localeInput['bridal_hero_subtitle'] ?? null,
                    'bridal_hero_description' => $localeInput['bridal_hero_description'] ?? null,
                    'bridal_services_label' => $localeInput['bridal_services_label'] ?? null,
                    'bridal_services_title' => $localeInput['bridal_services_title'] ?? null,
                    'bridal_services_cta_text' => $localeInput['bridal_services_cta_text'] ?? null,
                    'bridal_testimonials_label' => $localeInput['bridal_testimonials_label'] ?? null,
                    'bridal_testimonials_title' => $localeInput['bridal_testimonials_title'] ?? null,
                    'bridal_services' => $collections['services'] ? json_encode($collections['services']) : null,
                    'bridal_promos' => $collections['promos'] ? json_encode($collections['promos']) : null,
                    'bridal_testimonials' => $collections['testimonials'] ? json_encode($collections['testimonials']) : null,
                    'bridal_locations' => $collections['locations'] ? json_encode($collections['locations']) : null,
                ]
            );
        }

        // Limpa o cache do front-end
        Cache::forget('bridal_data');
        Cache::forget('bridal_active_brands');
        Cache::forget('bridal_active_products');
        Cache::forget('bridal_visible_catalog_v2_products');

        return redirect()->route('admin.bridal.index')->with('success', 'SAX Bridal atualizado com sucesso em PT, ES e EN!');
    }

    /**
     * Conversor a WebP: el service centraliza la lógica; aquí solo se pasa la ruta.
     */
    private function convertToWebp($image, $type)
    {
        return app(ImageConverterService::class)->toWebp($image, $type);
    }
}
