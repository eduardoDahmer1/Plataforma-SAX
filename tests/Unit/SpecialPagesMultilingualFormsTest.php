<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class SpecialPagesMultilingualFormsTest extends TestCase
{
    public function test_nested_multilingual_field_generates_all_locale_names(): void
    {
        $html = Blade::render(
            '<x-admin.lang-field name="bridal_promos][0][title" label="Título" pt="PT" es="ES" en="EN" />'
        );

        $this->assertStringContainsString('name="translate[pt-br][bridal_promos][0][title]"', $html);
        $this->assertStringContainsString('name="translate[es][bridal_promos][0][title]"', $html);
        $this->assertStringContainsString('name="translate[en][bridal_promos][0][title]"', $html);
        $this->assertSame(3, substr_count($html, 'data-lang-field-btn='));
    }

    public function test_every_special_page_form_uses_the_shared_language_guide(): void
    {
        foreach (['palace', 'bridal', 'cafe_bistro', 'institucional'] as $page) {
            $form = file_get_contents(resource_path("views/admin/{$page}/edit.blade.php"));
            $this->assertStringContainsString('x-admin.translation-guide', $form, "Guia ausente em {$page}");
        }
    }

    public function test_bridal_no_longer_uses_a_global_language_selector(): void
    {
        $form = file_get_contents(resource_path('views/admin/bridal/edit.blade.php'));
        $controller = file_get_contents(app_path('Http/Controllers/Admin/BridalAdminController.php'));

        $this->assertStringNotContainsString('name="locale"', $form);
        $this->assertStringNotContainsString('route(\'lang.switch\'', $form);
        $this->assertStringContainsString("foreach (['pt-br', 'es', 'en'] as \$locale)", $controller);
    }

    public function test_special_page_actions_and_seo_use_the_accessible_visual_classes(): void
    {
        $header = file_get_contents(resource_path('views/components/admin/sticky-header.blade.php'));
        $mobileSubmit = file_get_contents(resource_path('views/components/admin/mobile-submit.blade.php'));
        $bridal = file_get_contents(resource_path('views/admin/bridal/edit.blade.php'));

        $this->assertStringContainsString('sticky-header__cancel', $header);
        $this->assertStringContainsString('sticky-header__submit', $header);
        $this->assertStringContainsString('mobile-submit-bar', $mobileSubmit);
        $this->assertStringContainsString('special-page-seo', $bridal);
        $this->assertStringContainsString('special-page-form__footer', $bridal);
    }
}
