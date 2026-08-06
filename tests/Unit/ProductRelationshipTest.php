<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductRelationshipTest extends TestCase
{
    public function test_original_color_code_is_used_to_match_size_variants(): void
    {
        $parent = new Product([
            'external_name' => "BRIC'S MALETA BELLAGIO #76CM *698",
            'color' => '#A17C5B',
        ]);
        $variant = new Product([
            'external_name' => "BRIC'S MALETA BELLAGIO #55CM *698",
            'color' => '#A27D5C',
        ]);

        $this->assertSame("BRIC S MALETA BELLAGIO", $parent->relationshipReferenceKey());
        $this->assertSame($parent->relationshipReferenceKey(), $variant->relationshipReferenceKey());
        $this->assertSame('698', $parent->relationshipColorKey());
        $this->assertSame($parent->relationshipColorKey(), $variant->relationshipColorKey());
    }

    public function test_visual_color_is_the_fallback_without_an_original_code(): void
    {
        $product = new Product([
            'external_name' => "BRIC'S MALETA BELLAGIO",
            'color' => '#A17C5B',
        ]);

        $this->assertSame('A17C5B', $product->relationshipColorKey());
    }

    public function test_reference_size_and_color_are_extracted_from_go_name(): void
    {
        $product = new Product([
            'external_name' => 'MARCA MODELO AB12345 #M *001',
        ]);

        $this->assertSame('AB12345', $product->relationshipReferenceKey());
        $this->assertSame('M', $product->inferredSize());
        $this->assertSame('001', $product->relationshipColorKey());
    }
}
