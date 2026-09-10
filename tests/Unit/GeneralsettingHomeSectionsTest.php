<?php

namespace Tests\Unit;

use App\Models\Generalsetting;
use PHPUnit\Framework\TestCase;

class GeneralsettingHomeSectionsTest extends TestCase
{
    public function test_it_resolves_saved_visibility_and_order_for_every_home_section(): void
    {
        $settings = new Generalsetting();
        $settings->home_sections = [
            'newsletter' => ['enabled' => false, 'position' => 1],
            'main_slider' => ['enabled' => true, 'position' => 2],
        ];

        $sections = $settings->resolvedHomeSections();

        $this->assertCount(10, $sections);
        $this->assertSame('newsletter', array_key_first($sections));
        $this->assertFalse($sections['newsletter']['enabled']);
        $this->assertSame('main_slider', array_keys($sections)[1]);
    }

    public function test_it_provides_a_complete_default_home_structure(): void
    {
        $sections = Generalsetting::defaultHomeSections();

        $this->assertSame(range(1, 10), array_column($sections, 'position'));
        $this->assertNotContains(false, array_column($sections, 'enabled'), true);
    }

    public function test_it_resolves_custom_section_content_in_each_language(): void
    {
        $settings = new Generalsetting();
        $settings->home_sections = [
            'most_viewed' => [
                'enabled' => true,
                'position' => 1,
                'content' => [
                    'pt' => ['title' => 'Mais desejados', 'description' => 'Descrição PT'],
                    'en' => ['title' => 'Most wanted', 'description' => 'English description'],
                    'es' => ['title' => 'Más deseados', 'description' => 'Descripción ES'],
                ],
            ],
        ];

        $section = $settings->resolvedHomeSections()['most_viewed'];

        $this->assertSame('Mais desejados', Generalsetting::contentForLocale($section, 'pt_BR')['title']);
        $this->assertSame('Most wanted', Generalsetting::contentForLocale($section, 'en')['title']);
        $this->assertSame('Más deseados', Generalsetting::contentForLocale($section, 'es')['title']);
    }

    public function test_it_preserves_empty_content_without_restoring_default_text(): void
    {
        $settings = new Generalsetting();
        $settings->home_sections = [
            'categories' => [
                'enabled' => true,
                'position' => 1,
                'content' => collect(['pt', 'en', 'es'])->mapWithKeys(fn (string $language) => [
                    $language => ['title' => '', 'description' => ''],
                ])->all(),
            ],
        ];

        $content = Generalsetting::contentForLocale($settings->resolvedHomeSections()['categories'], 'pt_BR');

        $this->assertSame('', $content['title']);
        $this->assertSame('', $content['description']);
    }

    public function test_it_resolves_the_category_display_limit(): void
    {
        $settings = new Generalsetting();
        $settings->home_sections = ['categories' => ['category_limit' => '3']];

        $this->assertSame('3', $settings->resolvedHomeSections()['categories']['category_limit']);

        $settings->home_sections = ['categories' => ['category_limit' => 'invalid']];

        $this->assertSame('all', $settings->resolvedHomeSections()['categories']['category_limit']);
    }

    public function test_it_resolves_custom_help_labels_in_each_language(): void
    {
        $settings = new Generalsetting();
        $settings->home_sections = [
            'help' => [
                'items' => [
                    ['pt' => 'Originais', 'en' => 'Original', 'es' => 'Originales'],
                ],
            ],
        ];

        $item = $settings->resolvedHomeSections()['help']['items'][0];

        $this->assertSame('Originais', Generalsetting::helpItemLabelForLocale($item, 'pt_BR'));
        $this->assertSame('Original', Generalsetting::helpItemLabelForLocale($item, 'en'));
        $this->assertSame('Originales', Generalsetting::helpItemLabelForLocale($item, 'es'));
    }
}
