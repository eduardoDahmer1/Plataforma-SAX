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
     * Exibe a visão geral dos dados atuais.
     */
    public function index()
    {
        // Traz o registro junto com as traduções para a listagem
        $palace = Palace::with('translations')->first() ?? Palace::create(['hero_titulo' => 'SAX Palace']);
        return view('admin.palace.index', compact('palace'));
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit($id)
    {
        // Carrega o Palace com suas traduções existentes para preencher o form
        $palace = Palace::with('translations')->findOrFail($id);
        return view('admin.palace.edit', compact('palace'));
    }

    /**
     * Processa a atualização de todos os campos, imagens e traduções.
     */
    public function update(Request $request, $id)
    {
        $palace = Palace::findOrFail($id);

        // Validamos também o campo obrigatório do idioma que está sendo editado/salvo
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

            'eventos_galeria' => 'nullable|array',
            'eventos_galeria.*' => 'image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',

            'gastronomia_menu_pdf' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        // Separa o idioma selecionado
        $locale = $data['locale'];

        // 1. Processar Imagens Individuais (Substituição com Conversão WebP)
        $fileFields = ['hero_imagem', 'bar_imagem_1', 'bar_imagem_2', 'bar_imagem_3', 'tematica_imagem'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($palace->$field) {
                    Storage::disk('public')->delete($palace->$field);
                }
                $data[$field] = $this->convertToWebp($request->file($field), 'palace');
            }
        }

        // 2. Processar Galeria de Eventos
        if ($request->hasFile('eventos_galeria')) {
            if ($palace->eventos_galeria) {
                $oldGallery = is_array($palace->eventos_galeria) ? $palace->eventos_galeria : json_decode($palace->eventos_galeria, true);
                foreach ($oldGallery ?? [] as $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $galleryPaths = [];
            foreach ($request->file('eventos_galeria') as $image) {
                $galleryPaths[] = $this->convertToWebp($image, 'palace/galeria');
            }
            $data['eventos_galeria'] = $galleryPaths;
        }

        // 3. Processar o Arquivo do Cardápio em PDF
        if ($request->hasFile('gastronomia_menu_pdf')) {
            if ($palace->gastronomia_menu_pdf && Storage::disk('public')->exists($palace->gastronomia_menu_pdf)) {
                Storage::disk('public')->delete($palace->gastronomia_menu_pdf);
            }

            $pdfPath = $request->file('gastronomia_menu_pdf')->store('menus', 'public');
            $data['gastronomia_menu_pdf'] = $pdfPath;
        }

        // 4. Salva os dados globais na tabela pai (links, imagens, PDFs, etc)
        $palace->update($data);

        // 5. SALVA OU ATUALIZA A TRADUÇÃO NA TABELA POLIMÓRFICA ÚNICA
        $palace->translations()->updateOrCreate(
            [
                'locale' => $locale,
                'page_type' => 'palace', // ou o nome da Model se usar o morphMap do Laravel
            ],
            [
                'palace_hero_titulo'             => $data['hero_titulo'] ?? null,
                'palace_hero_descricao'          => $data['hero_descricao'] ?? null,
                'palace_bar_titulo'              => $data['bar_titulo'] ?? null,
                'palace_bar_descricao'           => $data['bar_descricao'] ?? null,
                'palace_eventos_titulo'          => $data['eventos_titulo'] ?? null,
                'palace_eventos_descricao'       => $data['eventos_descricao'] ?? null,
                'palace_tematica_tag'            => $data['tematica_tag'] ?? null,
                'palace_tematica_titulo'         => $data['tematica_titulo'] ?? null,
                'palace_tematica_descricao'      => $data['tematica_descricao'] ?? null,
                'palace_tematica_preco'          => $data['tematica_preco'] ?? null,
                'palace_gastronomia_titulo'      => $data['gastronomia_titulo'] ?? null,
                'palace_gastronomia_cafe_desc'   => $data['gastronomia_cafe_desc'] ?? null,
                'palace_gastronomia_almoco_desc' => $data['gastronomia_almoco_desc'] ?? null,
                'palace_gastronomia_jantar_desc' => $data['gastronomia_jantar_desc'] ?? null,
                'palace_contato_endereco'        => $data['contato_endereco'] ?? null,
                'palace_contato_horario_segunda' => $data['contato_horario_segunda'] ?? null,
                'palace_contato_horario_sabado'  => $data['contato_horario_sabado'] ?? null,
                'palace_contato_horario_domingo' => $data['contato_horario_domingo'] ?? null,
            ]
        );

        // 6. Limpa o Cache do Front-end para refletir as alterações instantaneamente
        Cache::forget('palace_data');

        return redirect()->route('admin.palace.index')->with('success', 'Conteúdo e tradução (' . strtoupper($locale) . ') do SAX Palace atualizados com sucesso!');
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