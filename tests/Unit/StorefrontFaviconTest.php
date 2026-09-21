<?php

namespace Tests\Unit;

use App\Services\StoreControlService;
use App\Services\StorefrontFaviconService;
use App\Services\StorefrontLayoutService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StorefrontFaviconTest extends TestCase
{
    public function test_each_installation_uses_its_own_valid_default_icon(): void
    {
        Schema::shouldReceive('hasTable')->andReturn(false);
        $favicons = new StorefrontFaviconService();

        $this->app->instance(StoreControlService::class, $this->storeControl('sax'));
        $this->assertSame(asset('images/favicons/sax.svg'), $favicons->url());

        $this->app->instance(StoreControlService::class, $this->storeControl('otica'));
        $this->assertSame(asset('images/favicons/vista.svg'), $favicons->url());

        foreach (['sax', 'vista'] as $layout) {
            $path = public_path('images/favicons/'.$layout.'.svg');
            $this->assertGreaterThan(0, filesize($path));
            $this->assertNotFalse(simplexml_load_file($path));
        }
    }

    public function test_stage_follows_its_selected_layout(): void
    {
        Schema::shouldReceive('hasTable')->andReturn(false);
        $this->app->instance(StoreControlService::class, $this->storeControl('stage'));
        Cache::put(StorefrontLayoutService::CACHE_KEY, 'vista', now()->addMinute());

        $this->assertSame(asset('images/favicons/vista.svg'), (new StorefrontFaviconService())->url());

        Cache::put(StorefrontLayoutService::CACHE_KEY, 'sax', now()->addMinute());
        $this->assertSame(asset('images/favicons/sax.svg'), (new StorefrontFaviconService())->url());
    }

    public function test_shared_head_and_admin_panel_use_layout_specific_favicons(): void
    {
        $head = file_get_contents(resource_path('views/components/head-master.blade.php'));
        $admin = file_get_contents(resource_path('views/admin/theme-settings/edit.blade.php'));

        $this->assertStringContainsString('StorefrontFaviconService::class)->url($headerLayout)', $head);
        $this->assertStringNotContainsString("asset('favicon.ico')", $head);
        $this->assertStringContainsString('Ícone da aba do navegador', $admin);
        $this->assertStringContainsString('theme-settings.favicon.update', $admin);
    }

    public function test_shared_head_compiles_the_favicon_without_leaving_raw_blade_directives(): void
    {
        $compiled = Blade::compileString(file_get_contents(resource_path('views/components/head-master.blade.php')));

        $this->assertStringContainsString('<?php echo e($faviconUrl); ?>', $compiled);
        $this->assertStringContainsString('<?php if($themeFontUrl): ?>', $compiled);
        $this->assertStringNotContainsString('href="{{ $faviconUrl }}"', $compiled);
    }

    private function storeControl(string $profile): StoreControlService
    {
        return new class($profile) extends StoreControlService
        {
            public function __construct(private readonly string $profile) {}

            public function storeProfile(): string
            {
                return $this->profile;
            }
        };
    }
}
