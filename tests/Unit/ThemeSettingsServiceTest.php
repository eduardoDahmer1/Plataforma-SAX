<?php

namespace Tests\Unit;

use App\Services\ThemeSettingsService;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class ThemeSettingsServiceTest extends TestCase
{
    public function test_each_special_page_has_an_independent_default_palette(): void
    {
        $service = new ThemeSettingsService();

        $this->assertSame('#681711', $service->defaults('palace')['primary_color']);
        $this->assertSame('#54152d', $service->defaults('bistro_asuncion')['primary_color']);
        $this->assertSame('#172741', $service->defaults('bistro_pjc')['primary_color']);
        $this->assertNotSame(
            $service->defaults('palace')['background_color'],
            $service->defaults('bridal')['background_color']
        );
    }

    public function test_normalization_rejects_unsafe_values_and_limits_font_size(): void
    {
        $service = new ThemeSettingsService();
        $settings = $service->normalize('storefront', [
            'primary_color' => 'red; background:url(javascript:alert(1))',
            'heading_font' => 'Injected Font',
            'heading_weight' => '1000',
            'base_font_size' => '99',
        ]);

        $this->assertSame('#111111', $settings['primary_color']);
        $this->assertSame('original', $settings['heading_font']);
        $this->assertSame('original', $settings['heading_weight']);
        $this->assertSame('20', $settings['base_font_size']);
    }

    public function test_blog_path_is_isolated_from_the_storefront(): void
    {
        $service = new ThemeSettingsService();

        $this->assertSame('blog', $service->scopeForRequest(Request::create('/blogs/artigo')));
        $this->assertSame('storefront', $service->scopeForRequest(Request::create('/search')));
    }

    public function test_generated_css_uses_sanitized_variables_and_scoped_storefront_selector(): void
    {
        $service = new class extends ThemeSettingsService {
            public function active(string $scope): ?array
            {
                return array_replace($this->defaults($scope), [
                    'primary_color' => '#222222',
                    'link_color' => '#885522',
                ]);
            }
        };

        $css = $service->cssForRequest(Request::create('/search'));

        $this->assertStringContainsString(':root,body.sax-storefront{', $css);
        $this->assertStringContainsString('--sax-theme-primary:#222222', $css);
        $this->assertStringContainsString('body main a:not(.btn):not(.nav-link)', $css);
        $this->assertStringNotContainsString('font-family:var(--sax-theme-body-font)', $css);
    }

    public function test_untouched_defaults_generate_no_override_css(): void
    {
        $service = new class extends ThemeSettingsService {
            public function active(string $scope): ?array
            {
                return $this->defaults($scope);
            }
        };

        $this->assertSame('', $service->cssForRequest(Request::create('/palace')));
        $this->assertSame('original', $service->defaults('storefront')['body_font']);
        $this->assertSame('original', $service->defaults('palace')['header_font']);
    }

    public function test_footer_customization_does_not_override_header_or_fonts(): void
    {
        $service = new class extends ThemeSettingsService {
            public function active(string $scope): ?array
            {
                return array_replace($this->defaults($scope), ['footer_background_color' => '#121212']);
            }
        };

        $css = $service->cssForRequest(Request::create('/'));

        $this->assertStringContainsString('--sax-theme-footer-bg:#121212', $css);
        $this->assertStringContainsString('.sax-footer-refined', $css);
        $this->assertStringNotContainsString('--sax-theme-header-bg', $css);
        $this->assertStringNotContainsString('font-family:var(--sax-theme-body-font)', $css);
    }
}
