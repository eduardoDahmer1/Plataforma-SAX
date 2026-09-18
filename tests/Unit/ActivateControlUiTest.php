<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ActivateControlUiTest extends TestCase
{
    public function test_view_exposes_catalog_filters_pagination_and_collapsible_sections(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/activate/index.blade.php');

        foreach ([20, 30, 40, 50, 100] as $amount) {
            $this->assertStringContainsString((string) $amount, $view);
        }

        $this->assertStringContainsString('activate-alphabet', $view);
        $this->assertStringContainsString('data-letter=', $view);
        $this->assertStringContainsString('data-collapse', $view);
        $this->assertStringContainsString('data-pagination', $view);
        $this->assertStringContainsString('activateScope', $view);
        $this->assertStringContainsString('activateSort', $view);
    }

    public function test_interactions_persist_preferences_and_paginate_each_section(): void
    {
        $script = file_get_contents(__DIR__.'/../../public/js/activate-control.js');

        $this->assertStringContainsString('localStorage.setItem', $script);
        $this->assertStringContainsString('data-page-prev', $script);
        $this->assertStringContainsString('data-page-next', $script);
        $this->assertStringContainsString('setCollapsed', $script);
        $this->assertStringContainsString('initialLetter', $script);
    }
}
