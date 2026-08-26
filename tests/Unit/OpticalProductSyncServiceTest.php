<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Services\OpticalProductSyncService;
use Tests\TestCase;

class OpticalProductSyncServiceTest extends TestCase
{
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
