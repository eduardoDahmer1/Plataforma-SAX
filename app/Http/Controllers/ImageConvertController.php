<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class ImageConvertController extends Controller
{
    public function convertAllToWebp()
    {
        $folderPath = storage_path('app/public/uploads');

        // extensões suportadas para conversão
        $extensions = ['jpg', 'jpeg', 'png'];

        $files = File::allFiles($folderPath);

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            $originalPath = $file->getRealPath();

            if (in_array($extension, $extensions)) {
                $image = null;

                if ($extension === 'jpg' || $extension === 'jpeg') {
                    $image = imagecreatefromjpeg($originalPath);
                } elseif ($extension === 'png') {
                    $image = imagecreatefrompng($originalPath);
                    imagepalettetotruecolor($image); // melhora qualidade
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }

                if ($image) {
                    $newPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $originalPath);

                    imagewebp($image, $newPath, 80);
                    imagedestroy($image);
                }
            }
        }

        return back()->with('success', 'Conversão para WebP concluída!');
    }
}
