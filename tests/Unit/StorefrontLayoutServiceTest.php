<?php

namespace Tests\Unit;

use App\Services\StorefrontLayoutService;
use App\Services\StoreControlService;
use Illuminate\Support\Facades\Cache;
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

    public function test_store_profile_also_controls_home_and_available_layouts(): void
    {
        $this->app->instance(StoreControlService::class, $this->storeControl('sax'));
        $layouts = new StorefrontLayoutService();

        $this->assertSame('sax', $layouts->effective());
        $this->assertSame('home', $layouts->homeView());
        $this->assertSame(['sax'], array_keys($layouts->availableLayouts()));

        $this->app->instance(StoreControlService::class, $this->storeControl('otica'));

        $this->assertSame('vista', $layouts->effective());
        $this->assertSame('storefront.vista.home', $layouts->homeView());
        $this->assertSame(['vista'], array_keys($layouts->availableLayouts()));
    }

    public function test_public_layout_consumers_use_the_effective_installation_layout(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layout/layout.blade.php');
        $provider = file_get_contents(__DIR__.'/../../app/Providers/AppServiceProvider.php');

        $this->assertStringContainsString('$layoutService->effective()', $layout);
        $this->assertStringContainsString("app(StorefrontLayoutService::class)->effective()", $provider);
        $this->assertStringNotContainsString('$layoutService->current()', $layout);
    }

    public function test_stage_keeps_both_layouts_available_for_testing(): void
    {
        $this->app->instance(StoreControlService::class, $this->storeControl('stage'));
        Cache::put(StorefrontLayoutService::CACHE_KEY, 'vista', now()->addMinute());
        $layouts = new StorefrontLayoutService();

        $this->assertSame('vista', $layouts->effective());
        $this->assertSame(['sax', 'vista'], array_keys($layouts->availableLayouts()));

        Cache::forget(StorefrontLayoutService::CACHE_KEY);
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
