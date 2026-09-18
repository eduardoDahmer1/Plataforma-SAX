<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class OpticalHomeCategoryLayoutTest extends TestCase
{
    public function test_large_optical_cards_use_real_taxonomy_names(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/storefront/vista/audience-grid.blade.php');

        $this->assertStringContainsString("<strong>{{ \$item['label'] }}</strong>", $view);
        $this->assertStringNotContainsString("'Para mujer'", $view);
        $this->assertStringNotContainsString("'Para hombre'", $view);
        $this->assertStringNotContainsString("'Para niños'", $view);
    }

    public function test_both_optical_home_blocks_accept_explicit_item_selection(): void
    {
        $categories = file_get_contents(__DIR__.'/../../resources/views/storefront/vista/categories.blade.php');
        $audiences = file_get_contents(__DIR__.'/../../resources/views/storefront/vista/audience-grid.blade.php');
        $admin = file_get_contents(__DIR__.'/../../resources/views/admin/sections_home/index.blade.php');

        $this->assertStringContainsString("\$section['optical_item_keys']", $categories);
        $this->assertStringContainsString("\$section['optical_item_keys']", $audiences);
        $this->assertStringContainsString('Categorias do bloco superior', $admin);
        $this->assertStringContainsString('Categorias dos cards grandes', $admin);
    }

    public function test_optical_grids_define_layouts_for_every_item_count(): void
    {
        $css = file_get_contents(__DIR__.'/../../public/css/storefront-vista.css');

        foreach (['1', '2', '3', '4', 'many'] as $count) {
            $this->assertStringContainsString(".vista-optical-categories__grid--{$count}", $css);
            $this->assertStringContainsString(".vista-audiences--{$count}", $css);
        }
    }
}
