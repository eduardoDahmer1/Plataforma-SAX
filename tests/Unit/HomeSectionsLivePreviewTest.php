<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HomeSectionsLivePreviewTest extends TestCase
{
    public function test_admin_exposes_the_live_home_preview_controls(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/sections_home/index.blade.php');

        $this->assertStringContainsString('id="homeLivePreview"', $view);
        $this->assertStringContainsString('data-preview-device="desktop"', $view);
        $this->assertStringContainsString('data-preview-device="mobile"', $view);
        $this->assertStringContainsString('data-preview-language', $view);
        $this->assertStringContainsString('data-preview-sections', $view);
        $this->assertStringContainsString('home-sections-admin.js', $view);
        $this->assertStringContainsString('home-sections-admin.css', $view);
    }

    public function test_preview_reacts_to_form_order_visibility_content_and_taxonomy(): void
    {
        $script = file_get_contents(__DIR__.'/../../public/js/home-sections-admin.js');

        $this->assertStringContainsString("input[name=\"storefront_layout\"]:checked", $script);
        $this->assertStringContainsString("filter(isEnabled)", $script);
        $this->assertStringContainsString("optical_item_keys", $script);
        $this->assertStringContainsString('new MutationObserver', $script);
        $this->assertStringContainsString("beforeunload", $script);
        $this->assertStringContainsString("scrollIntoView", $script);
    }

    public function test_preview_has_desktop_mobile_and_layout_specific_presentations(): void
    {
        $css = file_get_contents(__DIR__.'/../../public/css/home-sections-admin.css');

        $this->assertStringContainsString('.home-preview-page.is-mobile', $css);
        $this->assertStringContainsString('.home-preview-page.is-vista', $css);
        $this->assertStringContainsString('.home-preview-grid--1', $css);
        $this->assertStringContainsString('.home-preview-grid--many', $css);
        $this->assertStringContainsString('.home-preview-tip.is-warning', $css);
    }
}
