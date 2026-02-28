<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bridal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BridalAdminController extends Controller
{
    /**
     * Dashboard resumen del contenido Bridal.
     */
    public function index()
    {
        $bridal = Bridal::first() ?? Bridal::create(['hero_title' => 'SAX Bridal']);
        return view('admin.bridal.index', compact('bridal'));
    }

    /**
     * Formulario de edición.
     */
    public function edit($id)
    {
        $bridal = Bridal::findOrFail($id);
        return view('admin.bridal.edit', compact('bridal'));
    }

    /**
     * Procesa la actualización de todos los campos e imágenes.
     */
    public function update(Request $request, $id)
    {
        $bridal = Bridal::findOrFail($id);

        $data = $request->validate([
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
        if ($request->has('services_items')) {
            $services = [];
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
            $data['services'] = $services;
        }
        unset($data['services_items']);

        // 3. Construir JSON de promos (3 bloques fijos, con imagen por ítem)
        if ($request->has('promos_items')) {
            $promos = [];
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
            $data['promos'] = $promos;
        }
        unset($data['promos_items']);

        // 4. Construir JSON de brands (N dinámico, con logo opcional)
        if ($request->has('brands_items')) {
            $brands = [];
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
            $data['brands'] = $brands;
        }
        unset($data['brands_items']);

        // 5. Construir JSON de locations (N dinámico, con imagen)
        if ($request->has('locations_items')) {
            $locations = [];    
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
            $data['locations'] = $locations;
        }
        unset($data['locations_items']);

        // 6. Construir JSON de testimonials (4 bloques fijos, con foto opcional)
        if ($request->has('testimonials_items')) {
            $testimonials = [];
            foreach ($request->input('testimonials_items', []) as $index => $item) {
                $fotoPath = $item['foto_path'] ?? null;
                if ($request->hasFile("testimonials_items.$index.foto")) {
                    $fotoPath = $this->convertToWebp(
                        $request->file("testimonials_items.$index.foto"),
                        'bridal/testimonials'
                    );
                }
                if (!empty($item['quote']) || !empty($item['author'])) { // Solo se requiere quote o author para mostrar el bloque
                    $testimonials[] = [
                        'quote'     => $item['quote'] ?? '',
                        'author'    => $item['author'] ?? '',
                        'foto'      => $fotoPath,
                        'ubicacion' => $item['ubicacion'] ?? '',
                    ];
                }
            }
            $data['testimonials'] = $testimonials;
        }
        unset($data['testimonials_items']);

        $bridal->update($data);

        return redirect()->route('admin.bridal.edit', $id)->with('success', 'Contenido de SAX Bridal actualizado con éxito.');
    }

    /**
     * Conversor Universal para WebP (Suporta >10 formatos via fallback)
     */
    private function convertToWebp($image, $type)
    {
        ini_set('memory_limit', '512M');

        $tempPath  = $image->getRealPath();
        $extension = strtolower($image->getClientOriginalExtension());
        $directory = rtrim($type, '/') . '/';
        $filename  = uniqid() . '.webp';
        $fullPath  = storage_path('app/public/' . $directory . $filename);

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        if ($extension === 'webp' || $extension === 'avif') {
            Storage::disk('public')->putFileAs($directory, $image, $filename);
            return "{$directory}{$filename}";
        }

        $imageResource = match ($extension) {
            'jpeg', 'jpg', 'jfif' => @imagecreatefromjpeg($tempPath),
            'png'                 => @imagecreatefrompng($tempPath),
            'gif'                 => @imagecreatefromgif($tempPath),
            'bmp'                 => @imagecreatefrombmp($tempPath),
            'webp'                => @imagecreatefromwebp($tempPath),
            'tga'                 => @imagecreatefromtga($tempPath),
            default               => @imagecreatefromstring(file_get_contents($tempPath)),
        };

        if (!$imageResource) {
            $origFilename = uniqid() . '.' . $extension;
            Storage::disk('public')->putFileAs($directory, $image, $origFilename);
            return "{$directory}{$origFilename}";
        }

        imagepalettetotruecolor($imageResource);
        imagealphablending($imageResource, true);
        imagesavealpha($imageResource, true);

        imagewebp($imageResource, $fullPath, 80);
        imagedestroy($imageResource);

        return "{$directory}{$filename}";
    }
}
