<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstitucionalAdminController extends Controller
{
    /**
     * Exibe a visão geral dos dados institucionais.
     */
    public function index()
    {
        // Busca o primeiro registro ou cria um inicial se estiver vazio
        $institucional = Institucional::first() ?? Institucional::create(['section_one_title' => 'SAX Institutional']);
        return view('admin.institucional.index', compact('institucional'));
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit($id)
    {
        $institucional = Institucional::findOrFail($id);
        return view('admin.institucional.edit', compact('institucional'));
    }

    /**
     * Atualiza os dados e processa as múltiplas galerias e imagens.
     */
    public function update(Request $request, $id)
    {
        $institucional = Institucional::findOrFail($id);

        $data = $request->validate([
            'section_one_title'        => 'nullable|string|max:255',
            'section_one_content'      => 'nullable|string',
            'text_section_one_title'   => 'nullable|string|max:255',
            'text_section_one_body'    => 'nullable|string',
            'text_section_two_title'   => 'nullable|string|max:255',
            'text_section_two_body'    => 'nullable|string',
            'text_section_three_title' => 'nullable|string|max:255',
            'text_section_three_body'  => 'nullable|string',
            'stat_brands_count'        => 'nullable|integer',
            'stat_sqm_count'           => 'nullable|integer',
            'stat_employees_count'     => 'nullable|integer',
            
            // Imagem Única
            'section_one_image'        => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:8192',
            
            // Múltiplas Imagens (Arrays)
            'top_sliders'              => 'nullable|array',
            'top_sliders.*'            => 'image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'brand_logos'              => 'nullable|array',
            'brand_logos.*'            => 'image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'gallery_images'           => 'nullable|array',
            'gallery_images.*'         => 'image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
        ]);

        // 1. Processar Imagem Principal da Seção 1
        if ($request->hasFile('section_one_image')) {
            if ($institucional->section_one_image) {
                Storage::disk('public')->delete($institucional->section_one_image);
            }
            $data['section_one_image'] = $this->convertToWebp($request->file('section_one_image'), 'institucional');
        }

        // 2. Processar Arrays de Imagens (Sliders, Logos e Galeria)
        $arrayFields = ['top_sliders', 'brand_logos', 'gallery_images'];
        foreach ($arrayFields as $field) {
            if ($request->hasFile($field)) {
                // Deleta imagens antigas do array físico
                if ($institucional->$field) {
                    foreach ($institucional->$field as $oldPath) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $newPaths = [];
                foreach ($request->file($field) as $image) {
                    $newPaths[] = $this->convertToWebp($image, "institucional/$field");
                }
                $data[$field] = $newPaths;
            }
        }

        $institucional->update($data);

        return redirect()->route('admin.institucional.index')->with('success', 'Dados institucionais atualizados com sucesso!');
    }

    /**
     * Conversor Universal para WebP com suporte a alta resolução.
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
            'png'                 => @imagecreatefrompng($tempPath),
            'gif'                 => @imagecreatefromgif($tempPath),
            'bmp'                 => @imagecreatefrombmp($tempPath),
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