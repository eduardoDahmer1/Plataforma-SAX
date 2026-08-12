<?php

namespace Tests\Unit;

use App\Services\ProductSearchService;
use PHPUnit\Framework\TestCase;

class ProductSearchServiceTest extends TestCase
{
    public function test_it_understands_product_color_brand_and_brazilian_size(): void
    {
        $descriptors = (new ProductSearchService())->descriptors('Camiseta preta Boss tamanho GG');

        $this->assertCount(4, $descriptors);
        $this->assertContains('camiseta', $descriptors[0]['terms']);
        $this->assertContains('shirt', $descriptors[0]['terms']);
        $this->assertContains('#000000', $descriptors[1]['hex']);
        $this->assertSame(['BOSS'], $descriptors[2]['terms']);
        $this->assertSame(['GG', 'XL'], $descriptors[3]['sizes']);
    }

    public function test_it_understands_shoe_size_and_ignores_search_connectors(): void
    {
        $descriptors = (new ProductSearchService())->descriptors('Tênis de cor marrom tamanho 39');

        $this->assertCount(3, $descriptors);
        $this->assertContains('sneaker', $descriptors[0]['terms']);
        $this->assertContains('#A52A2A', $descriptors[1]['hex']);
        $this->assertSame(['39'], $descriptors[2]['sizes']);
    }

    public function test_it_recognizes_gender_synonyms_and_sku(): void
    {
        $service = new ProductSearchService();
        $gender = $service->descriptors('óculos femenino');
        $sku = $service->descriptors('4673354');

        $this->assertContains('oculos', $gender[0]['terms']);
        $this->assertContains('feminino', $gender[1]['terms']);
        $this->assertSame(['4673354'], $sku[0]['terms']);
    }

    public function test_it_recognizes_spanish_and_english_color_aliases(): void
    {
        $service = new ProductSearchService();

        $this->assertContains('#FFFFFF', $service->descriptors('camisa blanca')[1]['hex']);
        $this->assertContains('#FF0000', $service->descriptors('tenis red')[1]['hex']);
    }

    public function test_it_understands_product_names_in_three_languages(): void
    {
        $service = new ProductSearchService();

        $bag = $service->descriptors('black Hugo Boss bag size M');
        $pants = $service->descriptors('pantalón negro Armani talla GG');
        $shoes = $service->descriptors('zapatos marrones 39');

        $this->assertContains('bolsa', $bag[3]['terms']);
        $this->assertSame(['M'], $bag[4]['sizes']);
        $this->assertContains('pants', $pants[0]['terms']);
        $this->assertSame(['GG', 'XL'], $pants[3]['sizes']);
        $this->assertContains('shoes', $shoes[0]['terms']);
        $this->assertSame(['39'], $shoes[2]['sizes']);
    }

    public function test_it_loads_real_colors_and_sizes_from_public_catalogs(): void
    {
        $service = new ProductSearchService();

        $navy = $service->descriptors('blazer azul marinho tamanho 3XL');
        $fragrance = $service->descriptors('perfume 7.5 ml');
        $eyewear = $service->descriptors('gafas 52-19-140');
        $ring = $service->descriptors('anel US 7.5');
        $oneSize = $service->descriptors('boné one size');

        $this->assertContains('#000080', $navy[1]['hex']);
        $this->assertSame(['3XL', 'XXXL'], $navy[2]['sizes']);
        $this->assertSame(['7.5ML'], $fragrance[1]['sizes']);
        $this->assertSame(['52-19-140'], $eyewear[1]['sizes']);
        $this->assertSame(['US 7.5'], $ring[1]['sizes']);
        $this->assertContains('ONE SIZE', $oneSize[1]['sizes']);
    }

    public function test_it_uses_catalog_taxonomy_vocabulary_in_three_languages(): void
    {
        $service = new ProductSearchService();

        $luggage = $service->descriptors('maleta de viaje negra');
        $jewelry = $service->descriptors('silver jewelry feminino');
        $wine = $service->descriptors('vino tinto 750ml');

        $this->assertContains('luggage', $luggage[0]['terms']);
        $this->assertContains('joias', $jewelry[1]['terms']);
        $this->assertContains('vinhos', $wine[0]['terms']);
        $this->assertSame(['750ML'], $wine[2]['sizes']);
    }

    public function test_it_accepts_more_descriptors_without_silently_cutting_normal_queries(): void
    {
        $descriptors = (new ProductSearchService())->descriptors(
            'camisa social masculina preta slim manga longa algodao premium Hugo Boss tamanho GG nova colecao'
        );

        $this->assertGreaterThan(10, count($descriptors));
        $this->assertLessThanOrEqual(18, count($descriptors));
    }
}
