<?php

namespace Tests\Unit;

use App\Models\HomeBanner;
use PHPUnit\Framework\TestCase;

class HomeBannerTranslationTest extends TestCase
{
    public function test_it_returns_the_content_for_the_requested_language(): void
    {
        $banner = new HomeBanner([
            'title_pt' => 'Título PT',
            'description_pt' => 'Descrição PT',
            'title_en' => 'English title',
            'description_en' => 'English description',
            'title_es' => 'Título ES',
            'description_es' => 'Descripción ES',
        ]);

        $this->assertSame('Título PT', $banner->translated('title', 'pt_BR'));
        $this->assertSame('English description', $banner->translated('description', 'en'));
        $this->assertSame('Título ES', $banner->translated('title', 'es'));
    }

    public function test_it_keeps_empty_slide_content_empty(): void
    {
        $banner = new HomeBanner(['title_pt' => null, 'description_pt' => null]);

        $this->assertSame('', $banner->translated('title', 'pt_BR'));
        $this->assertSame('', $banner->translated('description', 'pt_BR'));
    }
}
