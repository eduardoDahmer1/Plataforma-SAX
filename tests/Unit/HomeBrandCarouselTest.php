<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HomeBrandCarouselTest extends TestCase
{
    public function test_admin_declares_the_dedicated_four_by_five_carousel_image(): void
    {
        $form = file_get_contents(__DIR__.'/../../resources/views/admin/brands/partials/form.blade.php');
        $controller = file_get_contents(__DIR__.'/../../app/Http/Controllers/Admin/BrandControllerAdmin.php');

        $this->assertStringContainsString('home_carousel_image', $form);
        $this->assertStringContainsString('1080 × 1350 px', $form);
        $this->assertStringContainsString('dimensions:width=1080,height=1350', $controller);
    }

    public function test_home_carousel_uses_the_dedicated_image_with_logo_fallback(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/home-components/brands-grid.blade.php');
        $javascript = file_get_contents(__DIR__.'/../../public/js/home.js');
        $css = file_get_contents(__DIR__.'/../../public/css/app.css');

        $this->assertStringContainsString('$brand->home_carousel_image ?: $brand->image', $view);
        $this->assertStringContainsString('brand.home_carousel_image || brand.image', $javascript);
        $this->assertMatchesRegularExpression('/\.sax-home-wrapper \.sax-item img\s*\{[^}]*object-fit:\s*contain/s', $css);
    }
}
