<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductManufacturerSearchIdentityTest extends TestCase
{
    /**
     * @dataProvider productNames
     */
    public function test_it_extracts_numeric_manufacturer_reference_candidates(
        string $externalName,
        string $expectedName,
        array $expectedReferences,
    ): void {
        $product = new Product(['external_name' => $externalName]);

        $this->assertSame([
            'name' => $expectedName,
            'reference_candidates' => $expectedReferences,
        ], $product->manufacturerSearchIdentity());
    }

    public static function productNames(): array
    {
        return [
            'Ferrari numeric reference' => [
                'FERRARI PORTA MIUDEZAS 270034721 #U *RED',
                'FERRARI PORTA MIUDEZAS 270034721',
                ['270034721'],
            ],
            'Zegna alphanumeric reference' => [
                'E.ZEGNA CORBATA Z5E10TA51PS #U *BL1',
                'E.ZEGNA CORBATA Z5E10TA51PS',
                ['Z5E10TA51PS'],
            ],
            'all numbers before the variant markers' => [
                'MARCA MODELO 12 SERIE-34 5678 #40 *BEIGE',
                'MARCA MODELO 12 SERIE-34 5678',
                ['12', 'SERIE-34', '5678'],
            ],
            'multiword color is excluded' => [
                'FERRA ZAPATO FANTASY 8899 #7 *KARUN NERO',
                'FERRA ZAPATO FANTASY 8899',
                ['8899'],
            ],
        ];
    }
}
