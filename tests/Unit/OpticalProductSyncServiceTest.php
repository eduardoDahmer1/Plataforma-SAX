<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Services\OpticalProductSyncService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OpticalProductSyncServiceTest extends TestCase
{
    public function test_it_copies_gallery_files_from_normal_and_legacy_json(): void
    {
        $peerRoot = sys_get_temp_dir().'/optical-media-test-'.bin2hex(random_bytes(8));
        $sourceRoot = $peerRoot.'-source';
        Storage::set('public', Storage::build(['driver' => 'local', 'root' => $sourceRoot]));
        config()->set('catalog-sync.peer_storage_path', $peerRoot);
        $photo = 'products/photo/main.webp';
        $gallery = 'products/gallery/additional.webp';
        Storage::disk('public')->put($photo, 'photo-content');
        Storage::disk('public')->put($gallery, 'gallery-content');
        $copy = new \ReflectionMethod(OpticalProductSyncService::class, 'copyProductMedia');

        try {
            foreach ([json_encode([$gallery]), json_encode(json_encode([$gallery]))] as $rawGallery) {
                $product = new Product();
                $product->setRawAttributes(['photo' => $photo, 'gallery' => $rawGallery], true);
                $copy->invoke(app(OpticalProductSyncService::class), $product);

                $this->assertSame('photo-content', file_get_contents($peerRoot.'/'.$photo));
                $this->assertFileExists($peerRoot.'/'.$gallery);
                $this->assertSame('gallery-content', file_get_contents($peerRoot.'/'.$gallery));
                File::deleteDirectory($peerRoot);
            }
        } finally {
            File::deleteDirectory($peerRoot);
            File::deleteDirectory($sourceRoot);
        }
    }

    public function test_gallery_array_is_stored_as_a_single_json_array(): void
    {
        $paths = ['products/gallery/additional.webp'];
        $product = new Product();
        $product->gallery = $paths;

        $this->assertSame($paths, json_decode($product->getAttributes()['gallery'], true));
        $this->assertSame($paths, $product->gallery);
    }

    public function test_it_recognizes_the_erp_optical_category_reference(): void
    {
        config()->set('catalog-sync.optical_category_ref_code', '13');
        config()->set('catalog-sync.optical_category_slugs', ['optico', 'otica']);

        $category = (new Category())->forceFill(['id' => 149, 'ref_code' => '13', 'slug' => 'catalogo-legado']);
        $product = (new Product())->setRelation('category', $category);

        $this->assertTrue(app(OpticalProductSyncService::class)->isOptical($product));
    }

    public function test_it_rejects_a_non_optical_category(): void
    {
        config()->set('catalog-sync.optical_category_ref_code', '13');
        config()->set('catalog-sync.optical_category_slugs', ['optico', 'otica']);

        $category = (new Category())->forceFill(['id' => 140, 'ref_code' => '3', 'slug' => 'masculino']);
        $product = (new Product())->setRelation('category', $category);

        $this->assertFalse(app(OpticalProductSyncService::class)->isOptical($product));
    }
}
