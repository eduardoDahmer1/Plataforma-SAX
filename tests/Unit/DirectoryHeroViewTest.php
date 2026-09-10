<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DirectoryHeroViewTest extends TestCase
{
    public function test_it_renders_the_shared_editorial_header_without_carousel_controls(): void
    {
        $html = Blade::render(
            '<x-directory-hero eyebrow="SAX Selection" title="Nossas marcas" description="Excelência em cada detalhe" />'
        );

        $this->assertStringContainsString('sax-directory-intro', $html);
        $this->assertStringContainsString('Nossas marcas', $html);
        $this->assertStringContainsString('Excelência em cada detalhe', $html);
        $this->assertStringNotContainsString('data-carousel', $html);
        $this->assertStringNotContainsString('data-carousel-next', $html);
    }

    public function test_it_renders_the_shared_directory_search_contract(): void
    {
        $html = Blade::render(
            '<x-directory-search action="/marcas" placeholder="Buscar marca" value="SAX" clear-url="/marcas" />'
        );

        $this->assertStringContainsString('class="sax-directory-search"', $html);
        $this->assertStringContainsString('name="search"', $html);
        $this->assertStringContainsString('value="SAX"', $html);
        $this->assertStringContainsString('sax-directory-search__clear', $html);
    }
}
