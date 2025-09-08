<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ImageConvertController extends Controller
{
    public function convertAllToWebp()
    {
        $basePath = storage_path('app/public');

        $extensions = ['jpg', 'jpeg', 'png'];

        $folders = ['blog_images', 'blogs', 'config', 'images', 'uploads','categories'];

        foreach ($folders as $folder) {
            $folderPath = $basePath . '/' . $folder;

            if (!File::exists($folderPath)) {
                continue;
            }

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
                        imagepalettetotruecolor($image);
                        imagealphablending($image, true);
                        imagesavealpha($image, true);
                    }

                    if ($image) {
                        $newPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $originalPath);
                        imagewebp($image, $newPath, 90);
                        imagedestroy($image);

                        // Apagar imagem antiga
                        unlink($originalPath);

                        // Atualizar caminhos no banco de dados, se for da pasta "blogs"
                        $relativeOld = str_replace($basePath . '/', '', $originalPath);
                        $relativeNew = str_replace($basePath . '/', '', $newPath);

                        if ($folder === 'blogs') {
                            // Atualizar campo 'image' na tabela blogs
                            DB::table('blogs')
                                ->where('image', $relativeOld)
                                ->update(['image' => $relativeNew]);

                            // Atualizar o src da tag <img> dentro do campo content
                            DB::table('blogs')
                                ->where('content', 'like', '%' . $relativeOld . '%')
                                ->update([
                                    'content' => DB::raw("REPLACE(content, '$relativeOld', '$relativeNew')")
                                ]);
                        }
                    }
                }
            }
        }

        return back()->with('success', 'Imagens convertidas para WebP com sucesso e banco atualizado!');
    }
}
