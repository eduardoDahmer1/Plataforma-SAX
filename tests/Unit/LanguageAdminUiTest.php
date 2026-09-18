<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class LanguageAdminUiTest extends TestCase
{
    public function test_view_exposes_app_filters_summary_and_bulk_save(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/languages/index.blade.php');

        foreach ([20, 30, 40, 50, 100] as $amount) {
            $this->assertStringContainsString((string) $amount, $view);
        }

        $this->assertStringContainsString('languageFiltersToggle', $view);
        $this->assertStringContainsString('language-alphabet', $view);
        $this->assertStringContainsString('name="filtro"', $view);
        $this->assertStringContainsString('name="idioma"', $view);
        $this->assertStringContainsString('name="ordenar"', $view);
        $this->assertStringContainsString('languageSaveAll', $view);
        $this->assertStringContainsString('data-copy-key', $view);
    }

    public function test_controller_validates_supported_filters_and_page_sizes(): void
    {
        $controller = file_get_contents(__DIR__.'/../../app/Http/Controllers/Admin/LanguageControllerAdmin.php');

        $this->assertStringContainsString("['faltando', 'completas']", $controller);
        $this->assertStringContainsString("['pt', 'en', 'es']", $controller);
        $this->assertStringContainsString("['az', 'za', 'recentes']", $controller);
        $this->assertStringContainsString('[20, 30, 40, 50, 100]', $controller);
    }

    public function test_script_guards_unsaved_changes_and_persists_collapsed_filters(): void
    {
        $script = file_get_contents(__DIR__.'/../../public/js/languages-admin.js');

        $this->assertStringContainsString('beforeunload', $script);
        $this->assertStringContainsString('localStorage.setItem', $script);
        $this->assertStringContainsString('saveAllButton', $script);
        $this->assertStringContainsString('navigator.clipboard.writeText', $script);
    }
}
