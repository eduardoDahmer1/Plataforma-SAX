<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Palace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class PalaceAdminController extends Controller
{
    /**
     * Exibe a visão geral dos dados do Palace.
     */
    public function index()
    {
        $palace = Palace::with('translations')->first() ?? Palace::create(['hero_titulo' => 'SAX Palace']);
        return view('admin.palace.index', compact('palace'));
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit($id)
    {
        $palace = Palace::with('translations')->findOrFail($id);
        return view('admin.palace.edit', compact('palace'));
    }

    /**
     * Processa a atualização de imagens e traduções (PT, ES, EN).
     */
    public function update(Request $request, $id)
    {
        $palace = Palace::findOrFail($id);

        // 1. Validação
        $data = $request->validate([
                        'locale' => 'required|string|in:pt-br,es,en', // Define qual idioma o form está enviando

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
            'eventos_galeria'      => 'nullable|array',
            'eventos_galeria.*'    => 'image|mimes:jpg,jpeg,png,webp,avif,gif|max:4096',
            'gastronomia_menu_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'translate'            => 'required|array',
        ]);

        // 2. Processamento de Imagens Individuais
        $fileFields = ['hero_imagem', 'bar_imagem_1', 'bar_imagem_2', 'bar_imagem_3', 'tematica_imagem'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($palace->$field) Storage::disk('public')->delete($palace->$field);
                $palace->$field = $this->convertToWebp($request->file($field), 'palace');
            }
        }

        // 3. Galeria de Eventos
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

        // 4. PDF do Cardápio
        if ($request->hasFile('gastronomia_menu_pdf')) {
            if ($palace->gastronomia_menu_pdf) Storage::disk('public')->delete($palace->gastronomia_menu_pdf);
            $palace->gastronomia_menu_pdf = $request->file('gastronomia_menu_pdf')->store('menus', 'public');
        }

        $palace->save();

        // 5. ATUALIZAÇÃO DAS TRADUÇÕES (Multilíngue)
        $translationsInput = $request->input('translate', []);
        $localesMapeados = ['pt-br' => 'pt-br', 'es' => 'es', 'en' => 'en'];

        foreach ($localesMapeados as $formLocale => $dbLocale) {
            $fields = $translationsInput[$formLocale] ?? [];
            dd($fields);

            // Ajuste aqui: force o page_type como string fixa para coincidir com o banco
            $palace->translations()->updateOrCreate(
                [
                    'locale' => $dbLocale,
                    'page_type' => 'App\Models\Palace', // <--- Garanta que esta string é igual à do banco
                ],
                [
                    'palace_hero_titulo'           => $fields['palace_hero_titulo'] ?? null,
                    'palace_hero_descricao'        => $fields['palace_hero_descricao'] ?? null,
                    'palace_bar_titulo'            => $fields['palace_bar_titulo'] ?? null,
                    'palace_bar_descricao'         => $fields['palace_bar_descricao'] ?? null,
                    'palace_eventos_titulo'        => $fields['palace_eventos_titulo'] ?? null,
                    'palace_eventos_descricao'     => $fields['palace_eventos_descricao'] ?? null,
                    'palace_tematica_tag'          => $fields['palace_tematica_tag'] ?? null,
                    'palace_tematica_titulo'       => $fields['palace_tematica_titulo'] ?? null,
                    'palace_tematica_descricao'    => $fields['palace_tematica_descricao'] ?? null,
                    'palace_tematica_preco'        => $fields['palace_tematica_preco'] ?? null,
                    'palace_gastronomia_titulo'    => $fields['palace_gastronomia_titulo'] ?? null,
                    'palace_gastronomia_cafe_desc' => $fields['palace_gastronomia_cafe_desc'] ?? null,
                    'palace_gastronomia_almoco_desc'=> $fields['palace_gastronomia_almoco_desc'] ?? null,
                    'palace_gastronomia_jantar_desc'=> $fields['palace_gastronomia_jantar_desc'] ?? null,
                    'palace_contato_endereco'      => $fields['palace_contato_endereco'] ?? null,
                    'palace_contato_horario_segunda'=> $fields['palace_contato_horario_segunda'] ?? null,
                    'palace_contato_horario_sabado'=> $fields['palace_contato_horario_sabado'] ?? null,
                    'palace_contato_horario_domingo'=> $fields['palace_contato_horario_domingo'] ?? null,
                ]
            );
        }

        Cache::forget('palace_data');

        return redirect()->route('admin.palace.index')->with('success', 'SAX Palace atualizado com sucesso em todos os idiomas!');
    }

    /**
     * Conversor Universal para WebP
     */
    private function convertToWebp($image, $type)
    {
        ini_set('memory_limit', '512M');

        $tempPath = $image->getRealPath();
        $extension = strtolower($image->getClientOriginalExtension());
        $directory = rtrim($type, '/') . '/';
        $filename = uniqid() . '.webp';
        $fullPath = storage_path('app/public/' . $directory . $filename);

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        if ($extension === 'webp' || $extension === 'avif') {
            Storage::disk('public')->putFileAs($directory, $image, $filename);
            return "{$directory}{$filename}";
        }

        $imageResource = match ($extension) {
            'jpeg', 'jpg', 'jfif' => @imagecreatefromjpeg($tempPath),
            'png' => @imagecreatefrompng($tempPath),
            'gif' => @imagecreatefromgif($tempPath),
            'bmp' => @imagecreatefrombmp($tempPath),
            'webp' => @imagecreatefromwebp($tempPath),
            'tga' => @imagecreatefromtga($tempPath),
            default => @imagecreatefromstring(file_get_contents($tempPath)),
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