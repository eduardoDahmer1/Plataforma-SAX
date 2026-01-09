<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class ImageService
{
    /**
     * Converte uma imagem para WebP
     */
    public function convertToWebp($image, string $type, int $quality = 90): string
    {
        $temp = $image->getRealPath();
        $ext = strtolower($image->getClientOriginalExtension());

        $imgRes = match ($ext) {
            'jpeg', 'jpg' => imagecreatefromjpeg($temp),
            'png' => imagecreatefrompng($temp),
            'gif' => imagecreatefromgif($temp),
            'webp' => imagecreatefromwebp($temp),
            default => throw new \Exception("Formato de imagem não suportado: {$ext}")
        };

        // Preserva transparência para PNG
        if ($ext === 'png') {
            imagealphablending($imgRes, false);
            imagesavealpha($imgRes, true);
        }

        ob_start();
        imagewebp($imgRes, null, $quality);
        $webpData = ob_get_clean();
        imagedestroy($imgRes);

        $dir = "products/{$type}/";
        $fileName = uniqid() . '_' . time() . '.webp';
        
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        
        Storage::disk('public')->put("{$dir}{$fileName}", $webpData);

        return "{$dir}{$fileName}";
    }

    /**
     * Deleta uma imagem se não estiver sendo usada por outros produtos
     */
    public function deleteIfUnused(string $imagePath, int $currentProductId, string $type = 'photo'): bool
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            return false;
        }

        $usedElsewhere = match($type) {
            'photo' => Product::where('photo', $imagePath)
                ->where('id', '!=', $currentProductId)
                ->exists(),
            'gallery' => Product::where('gallery', 'like', "%{$imagePath}%")
                ->where('id', '!=', $currentProductId)
                ->exists(),
            default => false
        };

        if (!$usedElsewhere) {
            return Storage::disk('public')->delete($imagePath);
        }

        return false;
    }

    /**
     * Deleta múltiplas imagens
     */
    public function deleteMultiple(array $imagePaths, int $currentProductId, string $type = 'gallery'): int
    {
        $deleted = 0;
        
        foreach ($imagePaths as $imagePath) {
            if ($this->deleteIfUnused($imagePath, $currentProductId, $type)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Otimiza uma imagem existente
     */
    public function optimizeExisting(string $imagePath, int $quality = 85): bool
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            return false;
        }

        try {
            $fullPath = Storage::disk('public')->path($imagePath);
            $imgRes = imagecreatefromwebp($fullPath);

            ob_start();
            imagewebp($imgRes, null, $quality);
            $webpData = ob_get_clean();
            imagedestroy($imgRes);

            Storage::disk('public')->put($imagePath, $webpData);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Retorna o tamanho de uma imagem em KB
     */
    public function getImageSize(string $imagePath): ?int
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            return null;
        }

        return round(Storage::disk('public')->size($imagePath) / 1024);
    }

    /**
     * Valida se o arquivo é uma imagem válida
     */
    public function isValidImage($file): bool
    {
        $validMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($file->getMimeType(), $validMimes);
    }
}