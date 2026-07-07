<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeBistro;
use App\Services\ImageConverterService;
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

    public function update(Request $request, $id)
    {
        $cafeBistro = CafeBistro::findOrFail($id);

        $data = $request->validate([
            // No traducibles (tabla principal)
            'telefono'                => 'nullable|string|max:255',
            'instagram_url'           => 'nullable|string|max:255',
            'facebook_url'            => 'nullable|string|max:255',
            'mapa_embed'              => 'nullable|string',
            'hero_imagen'             => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:8192',
            'sobre_imagen'            => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'cardapio_pdf'            => 'nullable|mimes:pdf|max:8192',
            'cardapio_galeria'        => 'nullable|array|max:8',
            'cardapio_galeria.*'      => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'cardapio_galeria_actual' => 'nullable|array',
            'eventos_galeria'         => 'nullable|array',
            'eventos_galeria.*'       => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'eventos_galeria_actual'  => 'nullable|array',
            'eventos_tipos'           => 'nullable|array',
            'eventos_tipos.*'         => 'nullable|string|max:255',
            'horario_segunda'         => 'nullable|string|max:255',
            'horario_terca_quinta'    => 'nullable|string|max:255',
            'horario_sexta_sabado'    => 'nullable|string|max:255',
            'horario_domingo'         => 'nullable|string|max:255',

            // Traducibles: llegan como translate[locale][cafe_campo]
            'translate'               => 'nullable|array',
        ]);

        // 1. Imagens individuais (hero, sobre)
        $imageFields = ['hero_imagen', 'sobre_imagen'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($cafeBistro->$field) {
                    Storage::disk('public')->delete($cafeBistro->$field);
                }
                $data[$field] = $this->convertToWebp($request->file($field), 'cafe_bistro');
            }
        }

        // 2. Arquivo PDF do cardápio
        if ($request->hasFile('cardapio_pdf')) {
            if ($cafeBistro->cardapio_pdf) {
                Storage::disk('public')->delete($cafeBistro->cardapio_pdf);
            }
            $data['cardapio_pdf'] = $request->file('cardapio_pdf')->store('cafe_bistro/cardapio', 'public');
        }

        // 3. Galeria de Eventos
        $galeriaFinal = is_array($request->input('eventos_galeria_actual')) ? $request->input('eventos_galeria_actual') : [];
        if ($request->hasFile('eventos_galeria')) {
            foreach ($request->file('eventos_galeria') as $img) {
                $galeriaFinal[] = $this->convertToWebp($img, 'cafe_bistro/eventos');
            }
        }

        // 4. Galeria do Cardápio
        $cardapioGaleriaFinal = is_array($request->input('cardapio_galeria_actual')) ? $request->input('cardapio_galeria_actual') : [];
        if ($request->hasFile('cardapio_galeria')) {
            foreach ($request->file('cardapio_galeria') as $img) {
                $cardapioGaleriaFinal[] = $this->convertToWebp($img, 'cafe_bistro/cardapio');
            }
        }

        // 5. Campos NO traducibles → tabla principal (comunes a todos los idiomas)
        $cafeBistro->update([
            'telefono'         => $request->input('telefono'),
            'instagram_url'    => $request->input('instagram_url'),
            'facebook_url'     => $request->input('facebook_url'),
            'mapa_embed'       => $request->input('mapa_embed'),
            'hero_imagen'      => $data['hero_imagen'] ?? $cafeBistro->hero_imagen,
            'sobre_imagen'     => $data['sobre_imagen'] ?? $cafeBistro->sobre_imagen,
            'cardapio_pdf'     => $data['cardapio_pdf'] ?? $cafeBistro->cardapio_pdf,
            'cardapio_galeria' => $cardapioGaleriaFinal,
            'eventos_galeria'  => $galeriaFinal,
            'eventos_tipos'    => $request->input('eventos_tipos', []),
            'horarios'         => [
                'segunda'      => $request->input('horario_segunda'),
                'terca_quinta' => $request->input('horario_terca_quinta'),
                'sexta_sabado' => $request->input('horario_sexta_sabado'),
                'domingo'      => $request->input('horario_domingo'),
            ],
        ]);

        // 6. Campos traducibles → una fila de tradução por idioma
        $translate = $request->input('translate', []);
        foreach (['pt-br', 'es', 'en'] as $loc) {
            $fields = $translate[$loc] ?? [];

            $cafeBistro->translations()->updateOrCreate(
                [
                    'locale' => $loc,
                ],
                [
                    'cafe_meta_title'         => $fields['cafe_meta_title'] ?? null,
                    'cafe_meta_description'   => $fields['cafe_meta_description'] ?? null,
                    'cafe_hero_titulo'        => $fields['cafe_hero_titulo'] ?? null,
                    'cafe_hero_subtitulo'     => $fields['cafe_hero_subtitulo'] ?? null,
                    'cafe_sobre_titulo'       => $fields['cafe_sobre_titulo'] ?? null,
                    'cafe_sobre_texto'        => $fields['cafe_sobre_texto'] ?? null,
                    'cafe_cardapio_titulo'    => $fields['cafe_cardapio_titulo'] ?? null,
                    'cafe_cardapio_subtitulo' => $fields['cafe_cardapio_subtitulo'] ?? null,
                    'cafe_eventos_titulo'     => $fields['cafe_eventos_titulo'] ?? null,
                    'cafe_eventos_subtitulo'  => $fields['cafe_eventos_subtitulo'] ?? null,
                    'cafe_eventos_texto'      => $fields['cafe_eventos_texto'] ?? null,
                    'cafe_direccion'          => $fields['cafe_direccion'] ?? null,
                ]
            );
        }

        Cache::forget('cafe_bistro_data');

        return redirect()->route('admin.cafe_bistro.index')
            ->with('success', 'Conteúdo e traduções atualizados com sucesso.');
    }

    /**
     * Conversor a WebP: el service centraliza la lógica; aquí solo se pasa la ruta.
     */
    private function convertToWebp($image, $type)
    {
        return app(ImageConverterService::class)->toWebp($image, $type);
    }
}