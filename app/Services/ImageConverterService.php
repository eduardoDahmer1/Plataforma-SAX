<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageConverterService
{
    // Lado máximo (px) permitido antes de reduzir proporcionalmente — evita fotos de celular
    // (4000x3000+) gerarem WebPs pesados mesmo sem nenhum limite de tamanho de arquivo na validação.
    private const MAX_DIMENSION = 2000;

    /**
     * Convierte una imagen subida a WebP y la guarda en el disco público.
     * Si la imagen es muy grande (en dimensiones), se reduce proporcionalmente antes de
     * codificar — así no hace falta rechazar el archivo por tamaño, siempre se acepta.
     *
     * @param  UploadedFile  $image      Archivo subido.
     * @param  string        $directory  Carpeta destino ya resuelta (ej: 'brands/logo').
     * @param  array         $options    ['quality' => int (0-100, default 80),
     *                                    'strict'  => bool (default true),
     *                                    'filename' => string (nome opcional, sempre .webp)]
     * @return string|null   Ruta relativa dentro del disco 'public', o null si strict=false y falla.
     *
     * @throws \Exception  Cuando strict=true y el formato no se puede procesar.
     */
    public function toWebp(UploadedFile $image, string $directory, array $options = []): ?string
    {
        $quality = $options['quality'] ?? 80;
        $strict  = $options['strict'] ?? true;

        ini_set('memory_limit', '512M');

        $directory = rtrim($directory, '/') . '/';
        $extension = strtolower($image->getClientOriginalExtension());
        $requestedFilename = $options['filename'] ?? null;
        $filename = $requestedFilename
            ? pathinfo(basename($requestedFilename), PATHINFO_FILENAME) . '.webp'
            : uniqid() . '.webp';

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Imagick cobre AVIF/HEIC/TIFF e outros formatos que o GD deste servidor
        // não decodifica. O resultado administrativo continua sendo sempre WebP.
        if (extension_loaded('imagick')) {
            try {
                $imagick = new \Imagick($image->getRealPath());
                $imagick->setIteratorIndex(0);
                $imagick->setImageFormat('webp');
                $imagick->setImageCompressionQuality($quality);
                $imagick->stripImage();

                $width = $imagick->getImageWidth();
                $height = $imagick->getImageHeight();
                if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
                    $imagick->thumbnailImage(self::MAX_DIMENSION, self::MAX_DIMENSION, true, true);
                }

                $imagick->writeImage(storage_path('app/public/' . $directory . $filename));
                $imagick->clear();
                $imagick->destroy();

                return "{$directory}{$filename}";
            } catch (\Throwable $exception) {
                // O GD ainda pode processar formatos comuns caso o codec do Imagick falhe.
            }
        }

        $tempPath      = $image->getRealPath();
        $imageResource = match ($extension) {
            'jpeg', 'jpg', 'jfif' => @imagecreatefromjpeg($tempPath),
            'png'                 => @imagecreatefrompng($tempPath),
            'gif'                 => @imagecreatefromgif($tempPath),
            'bmp'                 => @imagecreatefrombmp($tempPath),
            'tga'                 => @imagecreatefromtga($tempPath),
            'webp'                => @imagecreatefromwebp($tempPath),
            default               => @imagecreatefromstring(file_get_contents($tempPath)),
        };

        // No se pudo crear el recurso de imagen.
        if (!$imageResource) {
            if ($strict) {
                throw new \Exception('Formato de imagem não suportado ou arquivo inválido.');
            }

            // Nunca salve uma imagem administrativa fora de WebP silenciosamente.
            return null;
        }

        // Preserva transparencia (PNG con canal alfa).
        imagepalettetotruecolor($imageResource);
        imagealphablending($imageResource, true);
        imagesavealpha($imageResource, true);

        $imageResource = $this->resizeIfTooLarge($imageResource, self::MAX_DIMENSION);

        $fullPath = storage_path('app/public/' . $directory . $filename);
        if (! imagewebp($imageResource, $fullPath, $quality)) {
            imagedestroy($imageResource);
            throw new \Exception('Não foi possível converter a imagem para WebP.');
        }
        imagedestroy($imageResource);

        return "{$directory}{$filename}";
    }

    // Reduz proporcionalmente se largura ou altura passarem de $maxDimension; senão devolve intacto.
    private function resizeIfTooLarge($imageResource, int $maxDimension)
    {
        $width = imagesx($imageResource);
        $height = imagesy($imageResource);

        if ($width <= $maxDimension && $height <= $maxDimension) {
            return $imageResource;
        }

        $ratio = min($maxDimension / $width, $maxDimension / $height);
        $newWidth = max((int) round($width * $ratio), 1);
        $newHeight = max((int) round($height * $ratio), 1);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($imageResource);

        return $resized;
    }
}
