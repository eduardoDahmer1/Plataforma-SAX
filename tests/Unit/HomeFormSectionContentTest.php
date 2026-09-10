<?php

namespace Tests\Unit;

use App\Models\Generalsetting;
use Tests\TestCase;

class HomeFormSectionContentTest extends TestCase
{
    public function test_shared_home_form_renders_outside_home_with_its_own_section_content(): void
    {
        $settings = new Generalsetting([
            'home_sections' => Generalsetting::defaultHomeSections(),
        ]);

        $html = view('home-components.form-home', [
            'settings' => $settings,
            'attribute' => null,
        ])->render();

        $this->assertStringContainsString('help-section', $html);
        $this->assertStringContainsString('newsletter-section', $html);
        $this->assertStringContainsString('Como podemos ajudar?', $html);
        $this->assertStringContainsString('Não perca nenhuma novidade', $html);
    }

    public function test_individual_sections_render_safely_without_section_content(): void
    {
        $helpHtml = view('home-components.help-section', ['attribute' => null])->render();
        $newsletterHtml = view('home-components.newsletter-section')->render();

        $this->assertStringContainsString('help-grid', $helpHtml);
        $this->assertStringContainsString('newsletter-form', $newsletterHtml);
    }
}
