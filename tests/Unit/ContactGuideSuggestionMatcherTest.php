<?php

namespace Tests\Unit;

use App\Services\ContactGuideSuggestionMatcher;
use PHPUnit\Framework\TestCase;

class ContactGuideSuggestionMatcherTest extends TestCase
{
    public function test_it_prioritizes_brand_matches(): void
    {
        $match = (new ContactGuideSuggestionMatcher)->matchValues(
            'Salvatore Ferragamo',
            ['Moda masculina'],
            ['Ferragamo'],
            ['Marcas de luxo'],
        );

        $this->assertSame('brand', $match);
    }

    public function test_it_matches_generic_sectors_from_product_taxonomy(): void
    {
        $matcher = new ContactGuideSuggestionMatcher;

        $this->assertSame('taxonomy', $matcher->matchValues(
            'Matiere Premiere',
            ['Perfumes', 'Perfumería de nicho'],
            [],
            ['Perfumaria'],
        ));
        $this->assertSame('taxonomy', $matcher->matchValues(
            'Le Creuset',
            ['casa', 'Electrodomésticos y Cocina', 'Ollas y sartenes'],
            [],
            ['Home'],
        ));
    }

    public function test_it_does_not_invent_a_destination_for_an_unrelated_sector(): void
    {
        $match = (new ContactGuideSuggestionMatcher)->matchValues(
            'Dom Perignon',
            ['bebidas', 'Champagne'],
            [],
            ['Perfumaria'],
        );

        $this->assertNull($match);
    }
}
