<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CatalogBannerRemovalTest extends TestCase
{
    public function test_active_catalog_view_has_no_internal_banner_rendering(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/catalog/show.blade.php');

        $this->assertStringNotContainsString('heroBannerUrl', $view);
        $this->assertStringNotContainsString('sideBannerUrl', $view);
        $this->assertStringNotContainsString('catalog-side-banner', $view);
    }

    public function test_catalog_models_no_longer_accept_banner_fields(): void
    {
        foreach (['Category.php', 'Subcategory.php', 'CategoriasFilhas.php', 'Brand.php'] as $model) {
            $contents = file_get_contents(__DIR__.'/../../app/Models/'.$model);
            $this->assertDoesNotMatchRegularExpression("/['\"](?:banner|internal_banner)['\"]/", $contents, $model);
        }
    }
}
