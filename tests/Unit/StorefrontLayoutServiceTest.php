<?php

namespace Tests\Unit;

use App\Services\StorefrontLayoutService;
use App\Services\StoreControlService;
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

    public function test_store_profile_controls_the_header_identity(): void
    {
        $this->app->instance(StoreControlService::class, $this->storeControl('otica'));
        $layouts = new StorefrontLayoutService();

        $this->assertSame('vista', $layouts->headerLayout());
        $this->assertSame('storefront.vista.header', $layouts->headerPartial());

        $this->app->instance(StoreControlService::class, $this->storeControl('sax'));

        $this->assertSame('sax', $layouts->headerLayout());
        $this->assertSame('components.header', $layouts->headerPartial());
    }

    private function storeControl(string $profile): StoreControlService
    {
        return new class($profile) extends StoreControlService
        {
            public function __construct(private readonly string $profile)
            {
            }

            public function storeProfile(): string
            {
                return $this->profile;
            }
        };
    }
}
