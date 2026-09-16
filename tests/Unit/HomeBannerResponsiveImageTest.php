<?php

namespace Tests\Unit;

use App\Models\HomeBanner;
use Tests\TestCase;

class HomeBannerResponsiveImageTest extends TestCase
{
    public function test_mobile_url_falls_back_to_desktop_when_mobile_image_is_empty(): void
    {
        $banner = new HomeBanner([
            'image' => 'desktop.webp',
            'mobile_image' => null,
        ]);

        $this->assertSame($banner->image_url, $banner->mobile_image_url);
    }

    public function test_mobile_url_uses_the_mobile_image_when_it_exists(): void
    {
        $banner = new HomeBanner([
            'image' => 'desktop.webp',
            'mobile_image' => 'mobile.webp',
        ]);

        $this->assertStringEndsWith('/storage/uploads/mobile.webp', $banner->mobile_image_url);
        $this->assertNotSame($banner->image_url, $banner->mobile_image_url);
    }
}
