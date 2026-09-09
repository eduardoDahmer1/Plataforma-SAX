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

    /**
     * @dataProvider multiwordColorNames
     */
    public function test_multiword_color_and_size_do_not_become_the_search_reference(string $name): void
    {
        $product = new Product(['external_name' => $name]);

        $this->assertSame('FERRA ZAPATO FANTASY', $product->referenceLabel());
        $this->assertSame('FERRA ZAPATO FANTASY', $product->relationshipReferenceKey());
        $this->assertSame('FERRA ZAPATO FANTASY', $product->relationshipSearchTerm());
        $this->assertSame('9M', $product->inferredSize());
        $this->assertSame('KARUN NERO', $product->inferredColorCode());
        $this->assertSame('KARUNNERO', $product->relationshipColorKey());
    }

    public static function multiwordColorNames(): array
    {
        return [
            'original catalog name' => ['FERRA ZAPATO FANTASY #9M *KARUN NERO'],
            'encoded space before color' => ['FERRA ZAPATO FANTASY #9M&#x20;*KARUN NERO'],
            'encoded space within color' => ['FERRA ZAPATO FANTASY #9M *KARUN&nbsp;NERO'],
            'escaped color marker' => ['FERRA ZAPATO FANTASY #9M \\*KARUN NERO'],
        ];
    }

    public function test_size_siblings_and_other_colors_share_the_model_but_preserve_their_variant(): void
    {
        $parent = new Product(['external_name' => 'FERRA ZAPATO FANTASY #9M *KARUN NERO']);
        $sibling = new Product(['external_name' => 'FERRA ZAPATO FANTASY #7M *KARUN NERO']);
        $otherColor = new Product(['external_name' => 'FERRA ZAPATO FANTASY #9M *KARUN BIANCO']);

        $this->assertSame($parent->relationshipReferenceKey(), $sibling->relationshipReferenceKey());
        $this->assertSame($parent->relationshipReferenceKey(), $otherColor->relationshipReferenceKey());
        $this->assertSame('7M', $sibling->inferredSize());
        $this->assertSame($parent->relationshipColorKey(), $sibling->relationshipColorKey());
        $this->assertSame('KARUNBIANCO', $otherColor->relationshipColorKey());
        $this->assertNotSame($parent->relationshipColorKey(), $otherColor->relationshipColorKey());
    }
}
