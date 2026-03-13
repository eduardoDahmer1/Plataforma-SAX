<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeBistro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CafeBistroAdminController extends Controller
{
    public function index()
    {
        $cafeBistro = CafeBistro::first() ?? CafeBistro::create(['hero_titulo' => 'SAX Café & Bistrô']);

        return view('admin.cafe_bistro.index', compact('cafeBistro'));
    }

    public function edit($id)
    {
        $cafeBistro = CafeBistro::findOrFail($id);

        return view('admin.cafe_bistro.edit', compact('cafeBistro'));
    }

    public function update(Request $request, $id)
    {
        $cafeBistro = CafeBistro::findOrFail($id);

        $data = $request->validate([
            // General
            'is_active'        => 'nullable|boolean',
            'whatsapp'         => 'nullable|string|max:255',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',

            // Hero
            'hero_imagen'   => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:8192',
            'hero_titulo'   => 'nullable|string|max:255',
            'hero_subtitulo' => 'nullable|string|max:255',

            // Sobre Nós
            'sobre_imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'sobre_titulo' => 'nullable|string|max:255',
            'sobre_texto'  => 'nullable|string',

            // Cardápio
            'cardapio_titulo'   => 'nullable|string|max:255',
            'cardapio_subtitulo' => 'nullable|string',
            'cardapio_pdf'      => 'nullable|mimes:pdf|max:8192',
            'cardapio_galeria'         => 'nullable|array|max:8',
            'cardapio_galeria.*'       => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'cardapio_galeria_actual'  => 'nullable|array',

            // Eventos
            'eventos_titulo'   => 'nullable|string|max:255',
            'eventos_subtitulo' => 'nullable|string|max:255',
            'eventos_texto'    => 'nullable|string',
            'eventos_tipos'    => 'nullable|array',
            'eventos_tipos.*'  => 'nullable|string|max:255',
            'eventos_galeria'        => 'nullable|array',
            'eventos_galeria.*'      => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'eventos_galeria_actual' => 'nullable|array',

            // Horários
            'horarios' => 'nullable|array',

            // Contacto
            'direccion'     => 'nullable|string|max:255',
            'telefono'      => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'facebook_url'  => 'nullable|string|max:255',
            'mapa_embed'    => 'nullable|string',
        ]);

        // 1. Imágenes individuales (hero, sobre) → convertir a WebP
        $imageFields = ['hero_imagen', 'sobre_imagen'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($cafeBistro->$field) {
                    Storage::disk('public')->delete($cafeBistro->$field);
                }
                $data[$field] = $this->convertToWebp($request->file($field), 'cafe_bistro');
            }
        }

        // 2. PDF del cardápio
        if ($request->hasFile('cardapio_pdf')) {
            if ($cafeBistro->cardapio_pdf) {
                Storage::disk('public')->delete($cafeBistro->cardapio_pdf);
            }
            $data['cardapio_pdf'] = $request->file('cardapio_pdf')
                ->store('cafe_bistro/cardapio', 'public');
        }

        // 3. Galería de eventos (array de imágenes)
        $galeriaActual = $request->input('eventos_galeria_actual', []);
        $galeriaFinal  = is_array($galeriaActual) ? $galeriaActual : [];

        if ($request->hasFile('eventos_galeria')) {
            foreach ($request->file('eventos_galeria') as $img) {
                $galeriaFinal[] = $this->convertToWebp($img, 'cafe_bistro/eventos');
            }
        }

        // Eliminar imágenes removidas por el admin
        $galeriaAnterior = $cafeBistro->eventos_galeria ?? [];
        foreach ($galeriaAnterior as $oldImg) {
            if (!in_array($oldImg, $galeriaFinal)) {
                Storage::disk('public')->delete($oldImg);
            }
        }

        $data['eventos_galeria'] = $galeriaFinal;
        unset($data['eventos_galeria_actual']);

        // 4. Galería del cardápio (array de imágenes)
        $cardapioGaleriaActual = $request->input('cardapio_galeria_actual', []);
        $cardapioGaleriaFinal  = is_array($cardapioGaleriaActual) ? $cardapioGaleriaActual : [];

        if ($request->hasFile('cardapio_galeria')) {
            foreach ($request->file('cardapio_galeria') as $img) {
                $cardapioGaleriaFinal[] = $this->convertToWebp($img, 'cafe_bistro/cardapio');
            }
        }

        // Eliminar imágenes removidas por el admin
        $cardapioGaleriaAnterior = $cafeBistro->cardapio_galeria ?? [];
        foreach ($cardapioGaleriaAnterior as $oldImg) {
            if (!in_array($oldImg, $cardapioGaleriaFinal)) {
                Storage::disk('public')->delete($oldImg);
            }
        }

        $data['cardapio_galeria'] = $cardapioGaleriaFinal;
        unset($data['cardapio_galeria_actual']);

        $cafeBistro->update($data);
        Cache::forget('cafe_bistro_data');

        return redirect()
            ->route('admin.cafe_bistro.index')
            ->with('success', 'Conteúdo do SAX Café & Bistrô atualizado com sucesso.');
    }

    // TODO: Extraer convertToWebp a un Service compartido (Palace, Bridal, CafeBistro)
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
