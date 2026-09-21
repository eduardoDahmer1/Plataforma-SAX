<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Imagick;
use ImagickPixel;
use Throwable;

class FaviconImageService
{
    public const MIME_TYPES = [
        'image/png', 'image/jpeg', 'image/webp', 'image/gif',
        'image/x-icon', 'image/vnd.microsoft.icon', 'application/ico', 'image/avif',
    ];

    public function convert(UploadedFile $file, string $layout): string
    {
        if (! extension_loaded('imagick')) {
            throw ValidationException::withMessages(['favicon' => 'O conversor de imagens não está disponível no servidor.']);
        }
        $mime = $file->getMimeType();
        if (! in_array($mime, self::MIME_TYPES, true)) {
            throw ValidationException::withMessages(['favicon' => 'Formato inválido. Use PNG, JPG/JPEG, WebP, GIF, ICO ou AVIF.']);
        }
        $readPath = in_array($mime, ['image/x-icon', 'image/vnd.microsoft.icon', 'application/ico'], true)
            ? 'ICO:'.$file->getRealPath()
            : $file->getRealPath();

        $source = null;
        $frame = null;
        $canvas = null;

        try {
            $source = new Imagick();
            $source->pingImage($readPath);
            $width = $source->getImageWidth();
            $height = $source->getImageHeight();

            if ($width < 1 || $height < 1 || $width > 8192 || $height > 8192 || $width * $height > 40_000_000) {
                throw ValidationException::withMessages(['favicon' => 'A imagem é grande demais. Envie uma imagem de até 8192 px por lado.']);
            }

            $source->clear();
            $source->readImage($readPath);
            $source->setIteratorIndex(0);
            $frame = $source->getImage();
            $frame->setImageFormat('png');
            $frame->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);

            $scale = min(128 / $frame->getImageWidth(), 128 / $frame->getImageHeight());
            $targetWidth = max(1, (int) round($frame->getImageWidth() * $scale));
            $targetHeight = max(1, (int) round($frame->getImageHeight() * $scale));
            $frame->resizeImage($targetWidth, $targetHeight, Imagick::FILTER_LANCZOS, 1);

            $canvas = new Imagick();
            $canvas->newImage(128, 128, new ImagickPixel('transparent'), 'png');
            $canvas->compositeImage($frame, Imagick::COMPOSITE_OVER,
                (int) floor((128 - $targetWidth) / 2), (int) floor((128 - $targetHeight) / 2));
            $canvas->stripImage();

            $path = 'favicons/'.$layout.'/'.Str::uuid().'.png';
            if (! Storage::disk('public')->put($path, $canvas->getImageBlob())) {
                throw new \RuntimeException('Não foi possível salvar o ícone.');
            }

            return $path;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['favicon' => 'Não foi possível converter este arquivo em ícone. Tente outra imagem.']);
        } finally {
            $canvas?->clear();
            $frame?->clear();
            $source?->clear();
        }
    }
}
