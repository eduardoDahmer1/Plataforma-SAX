<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class ImageConverterService
{
    /**
     * Convierte una imagen subida a WebP y la guarda en el disco público.
     *
     * @param  UploadedFile  $image      Archivo subido.
     * @param  string        $directory  Carpeta destino ya resuelta (ej: 'brands/logo').
     * @param  array         $options    ['quality' => int (0-100, default 80),
     *                                    'strict'  => bool (default false)]
     * @return string|null   Ruta relativa dentro del disco 'public', o null si strict=false y falla.
     *
     * @throws \Exception  Cuando strict=true y el formato no se puede procesar.
     */
    public function toWebp(UploadedFile $image, string $directory, array $options = []): ?string
    {
        $quality = $options['quality'] ?? 80;
        $strict  = $options['strict'] ?? false;

        ini_set('memory_limit', '512M');

        $directory = rtrim($directory, '/') . '/';
        $extension = strtolower($image->getClientOriginalExtension());
        $filename  = uniqid() . '.webp';

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Si ya viene en un formato moderno, se guarda tal cual sin reprocesar.
        if ($extension === 'webp' || $extension === 'avif') {
            $finalName = uniqid() . '.' . $extension;
            Storage::disk('public')->putFileAs($directory, $image, $finalName);
            return "{$directory}{$finalName}";
        }

        $tempPath      = $image->getRealPath();
        $imageResource = match ($extension) {
            'jpeg', 'jpg', 'jfif' => @imagecreatefromjpeg($tempPath),
            'png'                 => @imagecreatefrompng($tempPath),
            'gif'                 => @imagecreatefromgif($tempPath),
            'bmp'                 => @imagecreatefrombmp($tempPath),
            'tga'                 => @imagecreatefromtga($tempPath),
            default               => @imagecreatefromstring(file_get_contents($tempPath)),
        };

        // No se pudo crear el recurso de imagen.
        if (!$imageResource) {
            if ($strict) {
                throw new \Exception('Formato de imagem não suportado ou arquivo inválido.');
            }

            // Modo tolerante: guarda el original para no perder el archivo.
            $origFilename = uniqid() . '.' . $extension;
            Storage::disk('public')->putFileAs($directory, $image, $origFilename);
            return "{$directory}{$origFilename}";
        }

        // Preserva transparencia (PNG con canal alfa).
        imagepalettetotruecolor($imageResource);
        imagealphablending($imageResource, true);
        imagesavealpha($imageResource, true);

        $fullPath = storage_path('app/public/' . $directory . $filename);
        imagewebp($imageResource, $fullPath, $quality);
        imagedestroy($imageResource);

        return "{$directory}{$filename}";
    }
}
