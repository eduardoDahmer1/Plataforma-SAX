<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Palace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PalaceAdminController extends Controller
{
    /**
     * Exibe a visão geral dos dados atuais.
     */
    public function index()
    {
        $palace = Palace::first() ?? Palace::create(['hero_titulo' => 'SAX Palace']);
        return view('admin.palace.index', compact('palace'));
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit($id)
    {
        $palace = Palace::findOrFail($id);
        return view('admin.palace.edit', compact('palace'));
    }

    /**
     * Processa a atualização de todos os campos e imagens.
     */
    public function update(Request $request, $id)
    {
        $palace = Palace::findOrFail($id);

        // Aumentamos os limites de validação e tipos de arquivos (mais de 10 tipos suportados)
        $data = $request->validate([
            'hero_titulo'             => 'nullable|string|max:255',
            'hero_descricao'          => 'nullable|string',
            'bar_titulo'              => 'nullable|string|max:255',
            'bar_descricao'           => 'nullable|string',
            'eventos_titulo'          => 'nullable|string|max:255',
            'eventos_descricao'       => 'nullable|string',
            'tematica_tag'            => 'nullable|string|max:255',
            'tematica_titulo'         => 'nullable|string|max:255',
            'tematica_descricao'      => 'nullable|string',
            'tematica_preco'          => 'nullable|string|max:255',
            'gastronomia_titulo'      => 'nullable|string|max:255',
            'gastronomia_cafe_desc'   => 'nullable|string',
            'gastronomia_almoco_desc' => 'nullable|string',
            'gastronomia_jantar_desc' => 'nullable|string',
            'contato_endereco'        => 'nullable|string',
            'contato_horario_segunda' => 'nullable|string',
            'contato_horario_sabado'  => 'nullable|string',
            'contato_horario_domingo' => 'nullable|string',
            'contato_whatsapp'        => 'nullable|string',
            'contato_mapa_iframe'     => 'nullable|string',

            // Aceita uma vasta gama de formatos de imagem
            'hero_imagem'     => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:8192',
            'bar_imagem_1'    => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'bar_imagem_2'    => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'bar_imagem_3'    => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'tematica_imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            
            'eventos_galeria'   => 'nullable|array',
            'eventos_galeria.*' => 'image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
        ]);

        // 1. Processar Imagens Individuais (Substituição com Conversão WebP)
        $fileFields = ['hero_imagem', 'bar_imagem_1', 'bar_imagem_2', 'bar_imagem_3', 'tematica_imagem'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Remove arquivo antigo se existir
                if ($palace->$field) {
                    Storage::disk('public')->delete($palace->$field);
                }
                // Converte e salva
                $data[$field] = $this->convertToWebp($request->file($field), 'palace');
            }
        }

        // 2. Processar Galeria de Eventos
        if ($request->hasFile('eventos_galeria')) {
            // Limpa galeria antiga física
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

        $palace->update($data);

        return redirect()->route('admin.palace.index')->with('success', 'Conteúdo do SAX Palace atualizado com sucesso e imagens otimizadas!');
    }

    /**
     * Conversor Universal para WebP (Suporta >10 formatos via fallback)
     */
    private function convertToWebp($image, $type)
    {
        // Aumenta memória para processar banners ou imagens de alta resolução
        ini_set('memory_limit', '512M');

        $tempPath = $image->getRealPath();
        $extension = strtolower($image->getClientOriginalExtension());

        // Define o diretório baseado no contexto enviado (palace, palace/galeria, etc)
        $directory = rtrim($type, '/') . '/';

        $filename = uniqid() . '.webp';
        $fullPath = storage_path('app/public/' . $directory . $filename);

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Se já for webp ou avif (formatos de próxima geração), salva sem reprocessar para manter qualidade
        if ($extension === 'webp' || $extension === 'avif') {
            Storage::disk('public')->putFileAs($directory, $image, $filename);
            return "{$directory}{$filename}";
        }

        // Cria recurso de imagem suportando os 10+ tipos solicitados
        $imageResource = match ($extension) {
            'jpeg', 'jpg', 'jfif' => @imagecreatefromjpeg($tempPath),
            'png'                 => @imagecreatefrompng($tempPath),
            'gif'                 => @imagecreatefromgif($tempPath),
            'bmp'                 => @imagecreatefrombmp($tempPath),
            'webp'                => @imagecreatefromwebp($tempPath),
            'tga'                 => @imagecreatefromtga($tempPath),
            // Para outros formatos (TIFF, HEIC, etc), tenta via string ou fallback original
            default               => @imagecreatefromstring(file_get_contents($tempPath)),
        };

        if (!$imageResource) {
            // Fallback: se o GD não conseguir converter o formato exótico, salva o original para não perder o upload
            $origFilename = uniqid() . '.' . $extension;
            Storage::disk('public')->putFileAs($directory, $image, $origFilename);
            return "{$directory}{$origFilename}";
        }

        // Mantém transparência (essencial para PNG e GIF)
        imagepalettetotruecolor($imageResource);
        imagealphablending($imageResource, true);
        imagesavealpha($imageResource, true);

        // Salva otimizado em WebP
        imagewebp($imageResource, $fullPath, 80);
        imagedestroy($imageResource);

        return "{$directory}{$filename}";
    }
}