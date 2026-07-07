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
            'locale'    => 'required|string|in:pt-br,es,en', // Define o idioma enviado pelo form

            // Básicos
            'title'     => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',

            // Hero
            'hero_title'       => 'nullable|string|max:255',
            'hero_subtitle'    => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',

            // Services
            'services_label'    => 'nullable|string|max:255',
            'services_title'    => 'nullable|string|max:255',
            'services_cta_text' => 'nullable|string|max:255',
            'services_cta_link' => 'nullable|string|max:255',

            // Palace Banner
            'palace_subtitle'    => 'nullable|string|max:255',
            'palace_title'       => 'nullable|string|max:255',
            'palace_description' => 'nullable|string',
            'palace_link'        => 'nullable|string|max:255',

            // Testimonios
            'testimonials_label' => 'nullable|string|max:255',
            'testimonials_title' => 'nullable|string|max:255',

            // Instagram CTA
            'social_instagram' => 'nullable|string|max:255',

            // SEO
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',

            // Imágenes simples
            'hero_image'   => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:8192',
            'palace_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',

            // Locations (N dinámico, con imagen)
            'locations_items'                => 'nullable|array',
            'locations_items.*.name'         => 'nullable|string|max:255',
            'locations_items.*.address'      => 'nullable|string|max:255',
            'locations_items.*.phone'        => 'nullable|string|max:255',
            'locations_items.*.image_path'   => 'nullable|string',
            'locations_items.*.image'        => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',

            // Services (4 bloques fijos, con imagen)
            'services_items'               => 'nullable|array|max:4',
            'services_items.*.title'       => 'nullable|string|max:255',
            'services_items.*.description' => 'nullable|string',
            'services_items.*.image_path'  => 'nullable|string',
            'services_items.*.image'       => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',

            // Promos (3 bloques fijos)
            'promos_items'              => 'nullable|array|max:3',
            'promos_items.*.title'      => 'nullable|string|max:255',
            'promos_items.*.subtitle'   => 'nullable|string|max:255',
            'promos_items.*.button'     => 'nullable|string|max:255',
            'promos_items.*.link'       => 'nullable|string|max:255',
            'promos_items.*.image_path' => 'nullable|string',
            'promos_items.*.image'      => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',

            // Brands (N dinámico)
            'brands_items'               => 'nullable|array',
            'brands_items.*.nombre'      => 'nullable|string|max:255',
            'brands_items.*.logo_path'   => 'nullable|string',
            'brands_items.*.logo_imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:2048',

            // Testimonials (4 bloques fijos)
            'testimonials_items'             => 'nullable|array|max:4',
            'testimonials_items.*.quote'     => 'nullable|string|max:200',
            'testimonials_items.*.author'    => 'nullable|string|max:255',
            'testimonials_items.*.ubicacion' => 'nullable|string|max:255',
            'testimonials_items.*.foto_path' => 'nullable|string',
            'testimonials_items.*.foto'      => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:2048',
        ]);

        $locale = $data['locale'];

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

        // 2. Construir JSON de services (4 bloques fijos, con imagen)
        $services = [];
        if ($request->has('services_items')) {
            foreach ($request->input('services_items', []) as $index => $item) {
                $imagePath = $item['image_path'] ?? null;
                if ($request->hasFile("services_items.$index.image")) {
                    $imagePath = $this->convertToWebp(
                        $request->file("services_items.$index.image"),
                        'bridal/services'
                    );
                }
                if (!empty($item['title']) || !empty($item['description']) || $imagePath) {
                    $services[] = [
                        'image'       => $imagePath,
                        'title'       => $item['title'] ?? '',
                        'description' => $item['description'] ?? '',
                    ];
                }
            }
        }

        // 3. Construir JSON de promos (3 bloques fijos, con imagen por ítem)
        $promos = [];
        if ($request->has('promos_items')) {
            foreach ($request->input('promos_items', []) as $index => $item) {
                $imagePath = $item['image_path'] ?? null;
                if ($request->hasFile("promos_items.$index.image")) {
                    $imagePath = $this->convertToWebp(
                        $request->file("promos_items.$index.image"),
                        'bridal/promos'
                    );
                }
                if (!empty($item['title']) || !empty($item['subtitle']) || $imagePath) {
                    $promos[] = [
                        'image'    => $imagePath,
                        'title'    => $item['title'] ?? '',
                        'subtitle' => $item['subtitle'] ?? '',
                        'button'   => $item['button'] ?? '',
                        'link'     => $item['link'] ?? '',
                    ];
                }
            }
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
                if (!empty($item['nombre'])) {
                    $brands[] = [
                        'nombre'      => $item['nombre'],
                        'logo_imagen' => $logoPath,
                    ];
                }
            }
        }

        // 5. Construir JSON de locations (N dinámico, con imagen)
        $locations = [];
        if ($request->has('locations_items')) {
            foreach ($request->input('locations_items', []) as $index => $item) {
                $imagePath = $item['image_path'] ?? null;
                if ($request->hasFile("locations_items.$index.image")) {
                    $imagePath = $this->convertToWebp(
                        $request->file("locations_items.$index.image"),
                        'bridal/locations'
                    );
                }
                if (!empty($item['name'])) {
                    $phone = $item['phone'] ?? null;
                    $whatsappUrl = null;
                    if ($phone) {
                        $clean = preg_replace('/[^0-9]/', '', $phone);
                        $whatsappUrl = 'https://wa.me/' . $clean;
                    }

                    $locations[] = [
                        'name'         => $item['name'],
                        'address'      => $item['address'] ?? '',
                        'whatsapp_url' => $whatsappUrl,
                        'image'        => $imagePath,
                    ];
                }
            }
        }

        // 6. Construir JSON de testimonials (4 bloques fijos, con foto opcional)
        $testimonials = [];
        if ($request->has('testimonials_items')) {
            foreach ($request->input('testimonials_items', []) as $index => $item) {
                $fotoPath = $item['foto_path'] ?? null;
                if ($request->hasFile("testimonials_items.$index.foto")) {
                    $fotoPath = $this->convertToWebp(
                        $request->file("testimonials_items.$index.foto"),
                        'bridal/testimonials'
                    );
                }
                if (!empty($item['quote']) || !empty($item['author'])) {
                    $testimonials[] = [
                        'quote'     => $item['quote'] ?? '',
                        'author'    => $item['author'] ?? '',
                        'foto'      => $fotoPath,
                        'ubicacion' => $item['ubicacion'] ?? '',
                    ];
                }
            }
        }

        // Salva os dados estruturais e de controle globais na tabela pai
        $bridal->update([
            'title'             => $data['title'] ?? $bridal->title,
            'is_active'         => $data['is_active'] ?? $bridal->is_active,
            'services_cta_link' => $data['services_cta_link'] ?? $bridal->services_cta_link,
            'palace_link'        => $data['palace_link'] ?? $bridal->palace_link,
            'social_instagram'  => $data['social_instagram'] ?? $bridal->social_instagram,
            'hero_image'        => $data['hero_image'] ?? $bridal->hero_image,
            'palace_image'      => $data['palace_image'] ?? $bridal->palace_image,
            'promos'            => !empty($promos) ? $promos : $bridal->promos,
            'brands'            => !empty($brands) ? $brands : $bridal->brands,
        ]);

        // SALVA OU ATUALIZA TODOS OS TEXTOS TRADUZIDOS E ARRAYS MULTILÍNGUES NA TABELA ÚNICA
        $bridal->translations()->updateOrCreate(
            [
                'locale' => $locale,
                'page_type' => 'bridal',
            ],
            [
                'bridal_title'             => $data['title'] ?? null,
                'bridal_meta_title'        => $data['meta_title'] ?? null,
                'bridal_meta_description'   => $data['meta_description'] ?? null,
                'bridal_hero_title'        => $data['hero_title'] ?? null,
                'bridal_hero_subtitle'     => $data['hero_subtitle'] ?? null,
                'bridal_hero_description'  => $data['hero_description'] ?? null,
                'bridal_services_label'    => $data['services_label'] ?? null,
                'bridal_services_title'    => $data['services_title'] ?? null,
                'bridal_services_cta_text' => $data['services_cta_text'] ?? null,
                'bridal_palace_subtitle'   => $data['palace_subtitle'] ?? null,
                'bridal_palace_title'      => $data['palace_title'] ?? null,
                'bridal_palace_description'=> $data['palace_description'] ?? null,
                'bridal_testimonials_label'=> $data['testimonials_label'] ?? null,
                'bridal_testimonials_title'=> $data['testimonials_title'] ?? null,
                
                // Estruturas complexas salvas localizadas por idioma
                'bridal_services'          => !empty($services) ? json_encode($services) : null,
                'bridal_testimonials'      => !empty($testimonials) ? json_encode($testimonials) : null,
                'bridal_locations'         => !empty($locations) ? json_encode($locations) : null,
            ]
        );

        // Limpa o cache do front-end
        Cache::forget('bridal_data');

        return redirect()->route('admin.bridal.index')->with('success', 'Contenido y traducción (' . strtoupper($locale) . ') de SAX Bridal actualizados con éxito.');
    }

    /**
     * Conversor a WebP: el service centraliza la lógica; aquí solo se pasa la ruta.
     */
    private function convertToWebp($image, $type)
    {
        return app(ImageConverterService::class)->toWebp($image, $type);
    }
}