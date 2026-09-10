<?php

namespace Tests\Unit;

use App\Services\StorefrontLayoutService;
use Tests\TestCase;

class StorefrontLayoutServiceTest extends TestCase
{
    public function test_it_exposes_the_supported_storefront_layouts(): void
    {
        $this->assertSame(['sax', 'vista'], array_keys(StorefrontLayoutService::LAYOUTS));
    }

    public function test_it_normalizes_unknown_layouts_to_sax(): void
    {
        $layouts = app(StorefrontLayoutService::class);

        $this->assertSame('vista', $layouts->normalize('vista'));
        $this->assertSame('sax', $layouts->normalize('sax'));
        $this->assertSame('sax', $layouts->normalize('unknown'));
        $this->assertSame('sax', $layouts->normalize(null));
    }
}
