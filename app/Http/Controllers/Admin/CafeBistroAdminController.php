<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeBistro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CafeBistroAdminController extends Controller
{
    /**
     * Exibe a visão geral dos dados do Café & Bistrô.
     */
    public function index()
    {
        $cafeBistro = CafeBistro::with('translations')->first() ?? CafeBistro::create(['hero_titulo' => 'SAX Café & Bistrô']);

        return view('admin.cafe_bistro.index', compact('cafeBistro'));
    }

    /**
     * Exibe o formulário de edição com as abas de tradução.
     */
    public function edit($id)
    {
        $cafeBistro = CafeBistro::with('translations')->findOrFail($id);

        return view('admin.cafe_bistro.edit', compact('cafeBistro'));
    }

    /**
     * Atualiza os dados globais, processa uploads de mídia e persiste as traduções polimórficas.
     */
    public function update(Request $request, $id)
    {
        $cafeBistro = CafeBistro::findOrFail($id);

        $data = $request->validate([
            'locale'           => 'required|string|in:pt-br,es,en', // Identificador do idioma do form

            // General / Controle
            'is_active'        => 'nullable|boolean',
            'whatsapp'         => 'nullable|string|max:255',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',

            // Hero
            'hero_imagen'      => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:8192',
            'hero_titulo'      => 'nullable|string|max:255',
            'hero_subtitulo'   => 'nullable|string|max:255',

            // Sobre Nós
            'sobre_imagen'     => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'sobre_titulo'     => 'nullable|string|max:255',
            'sobre_texto'      => 'nullable|string',

            // Cardápio
            'cardapio_titulo'         => 'nullable|string|max:255',
            'cardapio_subtitulo'      => 'nullable|string',
            'cardapio_pdf'            => 'nullable|mimes:pdf|max:8192',
            'cardapio_galeria'        => 'nullable|array|max:8',
            'cardapio_galeria.*'      => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'cardapio_galeria_actual' => 'nullable|array',

            // Eventos
            'eventos_titulo'         => 'nullable|string|max:255',
            'eventos_subtitulo'      => 'nullable|string|max:255',
            'eventos_texto'          => 'nullable|string',
            'eventos_tipos'          => 'nullable|array',
            'eventos_tipos.*'        => 'nullable|string|max:255',
            'eventos_galeria'        => 'nullable|array',
            'eventos_galeria.*'      => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'eventos_galeria_actual' => 'nullable|array',

            // Horários
            'horarios'               => 'nullable|array',

            // Contacto
            'direccion'     => 'nullable|string|max:255',
            'telefono'      => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'facebook_url'  => 'nullable|string|max:255',
            'mapa_embed'    => 'nullable|string',
        ]);

        $locale = $data['locale'];

        // 1. Imagens individuais (hero, sobre) → converter para WebP
        $imageFields = ['hero_imagen', 'sobre_imagen'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($cafeBistro->$field) {
                    Storage::disk('public')->delete($cafeBistro->$field);
                }
                $data[$field] = $this->convertToWebp($request->file($field), 'cafe_bistro');
            }
        }

        // 2. Arquivo PDF do cardápio físico
        if ($request->hasFile('cardapio_pdf')) {
            if ($cafeBistro->cardapio_pdf) {
                Storage::disk('public')->delete($cafeBistro->cardapio_pdf);
            }
            $data['cardapio_pdf'] = $request->file('cardapio_pdf')->store('cafe_bistro/cardapio', 'public');
        }

        // 3. Galeria de Eventos (Array de imagens dinâmicas)
        $galeriaActual = $request->input('eventos_galeria_actual', []);
        $galeriaFinal  = is_array($galeriaActual) ? $galeriaActual : [];

        if ($request->hasFile('eventos_galeria')) {
            foreach ($request->file('eventos_galeria') as $img) {
                $galeriaFinal[] = $this->convertToWebp($img, 'cafe_bistro/eventos');
            }
        }

        // Remove do disco arquivos que o administrador retirou na UI
        $galeriaAnterior = $cafeBistro->eventos_galeria ?? [];
        foreach ($galeriaAnterior as $oldImg) {
            if (!in_array($oldImg, $galeriaFinal)) {
                Storage::disk('public')->delete($oldImg);
            }
        }
        $data['eventos_galeria'] = $galeriaFinal;

        // 4. Galeria Visual do Cardápio (Array de imagens)
        $cardapioGaleriaActual = $request->input('cardapio_galeria_actual', []);
        $cardapioGaleriaFinal  = is_array($cardapioGaleriaActual) ? $cardapioGaleriaActual : [];

        if ($request->hasFile('cardapio_galeria')) {
            foreach ($request->file('cardapio_galeria') as $img) {
                $cardapioGaleriaFinal[] = $this->convertToWebp($img, 'cafe_bistro/cardapio');
            }
        }

        // Remove do disco as imagens antigas descartadas da galeria do cardápio
        $cardapioGaleriaAnterior = $cafeBistro->cardapio_galeria ?? [];
        foreach ($cardapioGaleriaAnterior as $oldImg) {
            if (!in_array($oldImg, $cardapioGaleriaFinal)) {
                Storage::disk('public')->delete($oldImg);
            }
        }
        $data['cardapio_galeria'] = $cardapioGaleriaFinal;

        // 5. Persiste as alterações estruturais e globais de controle no modelo pai
        $cafeBistro->update([
            'is_active'     => $data['is_active'] ?? $cafeBistro->is_active,
            'whatsapp'      => $data['whatsapp'] ?? $cafeBistro->whatsapp,
            'hero_imagen'   => $data['hero_imagen'] ?? $cafeBistro->hero_imagen,
            'sobre_imagen'  => $data['sobre_imagen'] ?? $cafeBistro->sobre_imagen,
            'cardapio_pdf'  => $data['cardapio_pdf'] ?? $cafeBistro->cardapio_pdf,
            'instagram_url' => $data['instagram_url'] ?? $cafeBistro->instagram_url,
            'facebook_url'  => $data['facebook_url'] ?? $cafeBistro->facebook_url,
            'mapa_embed'    => $data['mapa_embed'] ?? $cafeBistro->mapa_embed,
        ]);

        // 6. PERSISTÊNCIA NA TABELA POLIMÓRFICA DE TRADUÇÃO (page_translations)
        $cafeBistro->translations()->updateOrCreate(
            [
                'locale'    => $locale,
                'page_type' => 'cafe_bistro',
            ],
            [
                'cafe_meta_title'        => $data['meta_title'] ?? null,
                'cafe_meta_description'  => $data['meta_description'] ?? null,
                'cafe_hero_titulo'       => $data['hero_titulo'] ?? null,
                'cafe_hero_subtitulo'    => $data['hero_subtitulo'] ?? null,
                'cafe_sobre_titulo'      => $data['sobre_titulo'] ?? null,
                'cafe_sobre_texto'       => $data['sobre_texto'] ?? null,
                'cafe_cardapio_titulo'    => $data['cardapio_titulo'] ?? null,
                'cafe_cardapio_subtitulo' => $data['cardapio_subtitulo'] ?? null,
                'cafe_eventos_titulo'    => $data['eventos_titulo'] ?? null,
                'cafe_eventos_subtitulo' => $data['eventos_subtitulo'] ?? null,
                'cafe_eventos_texto'     => $data['eventos_texto'] ?? null,
                'cafe_direccion'         => $data['direccion'] ?? null,
                'cafe_telefono'          => $data['telefono'] ?? null,

                // Componentes dinâmicos e arrays traduzíveis guardados como JSON localizados
                'cafe_eventos_tipos'     => isset($data['eventos_tipos']) ? json_encode($data['eventos_tipos']) : null,
                'cafe_horarios'          => isset($data['horarios']) ? json_encode($data['horarios']) : null,
                'cafe_cardapio_galeria'  => !empty($data['cardapio_galeria']) ? json_encode($data['cardapio_galeria']) : null,
                'cafe_eventos_galeria'   => !empty($data['eventos_galeria']) ? json_encode($data['eventos_galeria']) : null,
            ]
        );

        // 7. Limpeza do Cache no Frontend
        Cache::forget('cafe_bistro_data');

        return redirect()
            ->route('admin.cafe_bistro.index')
            ->with('success', 'Conteúdo e tradução (' . strtoupper($locale) . ') do SAX Café & Bistrô atualizados com sucesso.');
    }

    /**
     * Conversor Universal para WebP com suporte a alta resolução e múltiplos formatos.
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