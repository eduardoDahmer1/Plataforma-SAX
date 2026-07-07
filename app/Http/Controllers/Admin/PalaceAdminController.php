<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Palace;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class PalaceAdminController extends Controller
{
    public function index()
    {
        $palace = Palace::with('translations')->first() ?? Palace::create(['hero_titulo' => 'SAX Palace']);
        return view('admin.palace.index', compact('palace'));
    }

    public function edit($id)
    {
        $palace = Palace::with('translations')->findOrFail($id);
        return view('admin.palace.edit', compact('palace'));
    }

    public function update(Request $request, $id)
    {
        $palace = Palace::findOrFail($id);

        $request->validate([
            'hero_titulo' => 'nullable|string|max:255',
            'hero_descricao' => 'nullable|string',
            'bar_titulo' => 'nullable|string|max:255',
            'bar_descricao' => 'nullable|string',
            'eventos_titulo' => 'nullable|string|max:255',
            'eventos_descricao' => 'nullable|string',
            'tematica_tag' => 'nullable|string|max:255',
            'tematica_titulo' => 'nullable|string|max:255',
            'tematica_descricao' => 'nullable|string',
            'tematica_preco' => 'nullable|string|max:255',
            'gastronomia_titulo' => 'nullable|string|max:255',
            'gastronomia_cafe_desc' => 'nullable|string',
            'gastronomia_almoco_desc' => 'nullable|string',
            'gastronomia_jantar_desc' => 'nullable|string',
            'contato_endereco' => 'nullable|string',
            'contato_horario_segunda' => 'nullable|string',
            'contato_horario_sabado' => 'nullable|string',
            'contato_horario_domingo' => 'nullable|string',
            'contato_whatsapp' => 'nullable|string',
            'contato_mapa_iframe' => 'nullable|string',
            'hero_imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:8192',
            'bar_imagem_1' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'bar_imagem_2' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'bar_imagem_3' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'tematica_imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'eventos_galeria' => 'nullable|array',
            'eventos_galeria.*' => 'image|mimes:jpg,jpeg,png,webp,avif,gif|max:4096',
            'gastronomia_menu_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'translate' => 'required|array',
        ]);

        $fileFields = ['hero_imagem', 'bar_imagem_1', 'bar_imagem_2', 'bar_imagem_3', 'tematica_imagem'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($palace->$field) {
                    Storage::disk('public')->delete($palace->$field);
                }
                $palace->$field = $this->convertToWebp($request->file($field), 'palace');
            }
        }

        if ($request->hasFile('eventos_galeria')) {
            $oldGallery = is_array($palace->eventos_galeria) ? $palace->eventos_galeria : json_decode($palace->eventos_galeria ?? '[]', true);
            foreach ($oldGallery as $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            $galleryPaths = [];
            foreach ($request->file('eventos_galeria') as $image) {
                $galleryPaths[] = $this->convertToWebp($image, 'palace/galeria');
            }
            $palace->eventos_galeria = $galleryPaths;
        }

        if ($request->hasFile('gastronomia_menu_pdf')) {
            if ($palace->gastronomia_menu_pdf) {
                Storage::disk('public')->delete($palace->gastronomia_menu_pdf);
            }
            $palace->gastronomia_menu_pdf = $request->file('gastronomia_menu_pdf')->store('menus', 'public');
        }

        $palace->save();

        $translationsInput = $request->input('translate', []);
        $localesMapeados = ['pt-br' => 'pt-br', 'es' => 'es', 'en' => 'en'];

        foreach ($localesMapeados as $formLocale => $dbLocale) {
            $fields = $translationsInput[$formLocale] ?? [];

            $palace->translations()->updateOrCreate(
                [
                    'locale' => $dbLocale,
                    'page_type' => 'App\Models\Palace',
                ],
                [
                    'palace_hero_titulo' => $fields['palace_hero_titulo'] ?? null,
                    'palace_hero_descricao' => $fields['palace_hero_descricao'] ?? null,
                    'palace_bar_titulo' => $fields['palace_bar_titulo'] ?? null,
                    'palace_bar_descricao' => $fields['palace_bar_descricao'] ?? null,
                    'palace_eventos_titulo' => $fields['palace_eventos_titulo'] ?? null,
                    'palace_eventos_descricao' => $fields['palace_eventos_descricao'] ?? null,
                    'palace_tematica_tag' => $fields['palace_tematica_tag'] ?? null,
                    'palace_tematica_titulo' => $fields['palace_tematica_titulo'] ?? null,
                    'palace_tematica_descricao' => $fields['palace_tematica_descricao'] ?? null,
                    'palace_tematica_preco' => $fields['palace_tematica_preco'] ?? null,
                    'palace_gastronomia_titulo' => $fields['palace_gastronomia_titulo'] ?? null,
                    'palace_gastronomia_cafe_desc' => $fields['palace_gastronomia_cafe_desc'] ?? null,
                    'palace_gastronomia_almoco_desc' => $fields['palace_gastronomia_almoco_desc'] ?? null,
                    'palace_gastronomia_jantar_desc' => $fields['palace_gastronomia_jantar_desc'] ?? null,
                    'palace_contato_endereco' => $fields['palace_contato_endereco'] ?? null,
                    'palace_contato_horario_segunda' => $fields['palace_contato_horario_segunda'] ?? null,
                    'palace_contato_horario_sabado' => $fields['palace_contato_horario_sabado'] ?? null,
                    'palace_contato_horario_domingo' => $fields['palace_contato_horario_domingo'] ?? null,
                ]
            );
        }

        Cache::forget('palace_data');

        return redirect()->route('admin.palace.index')->with('success', 'SAX Palace atualizado com sucesso em todos os idiomas!');
    }

    private function convertToWebp($image, $type)
    {
        return app(ImageConverterService::class)->toWebp($image, $type);
    }
}