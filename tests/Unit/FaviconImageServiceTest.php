<?php

namespace Tests\Unit;

use App\Services\FaviconImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Imagick;
use ImagickPixel;
use Tests\TestCase;

class FaviconImageServiceTest extends TestCase
{
    public function test_common_image_formats_are_converted_to_a_square_png(): void
    {
        Storage::fake('public');
        $converter = new FaviconImageService();

        foreach (['PNG', 'JPEG', 'WEBP', 'GIF', 'ICO', 'AVIF'] as $format) {
            $source = new Imagick();
            $source->newImage(300, 100, new ImagickPixel('#d5a55b'));
            $source->setImageFormat($format === 'ICO' ? 'PNG' : $format);
            $blob = $source->getImageBlob();
            if ($format === 'ICO') {
                // ICO moderno: cabeçalho + uma imagem PNG interna.
                $blob = pack('vvvCCCCvvVV', 0, 1, 1, 32, 32, 0, 0, 1, 32, strlen($blob), 22).$blob;
            }
            $file = UploadedFile::fake()->createWithContent('favicon.'.strtolower($format), $blob);
            $source->clear();

            $this->assertContains($file->getMimeType(), FaviconImageService::MIME_TYPES, $format.' MIME');

            $path = $converter->convert($file, 'sax');

            $this->assertStringStartsWith('favicons/sax/', $path);
            $this->assertStringEndsWith('.png', $path);
            Storage::disk('public')->assertExists($path);
            $contents = Storage::disk('public')->get($path);
            $this->assertSame("\x89PNG\r\n\x1a\n", substr($contents, 0, 8));
            $this->assertSame([128, 128], array_slice(getimagesizefromstring($contents), 0, 2));
        }
    }

    public function test_invalid_file_is_rejected(): void
    {
        Storage::fake('public');
        $this->expectException(ValidationException::class);

        (new FaviconImageService())->convert(
            UploadedFile::fake()->createWithContent('favicon.txt', 'not an image'),
            'vista'
        );
    }
}
