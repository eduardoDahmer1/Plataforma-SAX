<?php

namespace Tests\Unit;

use App\Services\Dhl\DhlMeasurementRuleDefaults;
use PHPUnit\Framework\TestCase;

class DhlMeasurementRuleDefaultsTest extends TestCase
{
    /** @dataProvider recommendations */
    public function test_it_recommends_realistic_initial_profiles(string $name, string $profile, float $weight, bool $manualReview): void
    {
        $result = (new DhlMeasurementRuleDefaults())->recommend($name);

        $this->assertSame($profile, $result['profile_code']);
        $this->assertSame($weight, $result['weight_kg']);
        $this->assertSame($manualReview, $result['requires_manual_review']);
        $this->assertGreaterThan(0, $result['length_cm']);
        $this->assertGreaterThan(0, $result['width_cm']);
        $this->assertGreaterThan(0, $result['height_cm']);
    }

    public static function recommendations(): array
    {
        return [
            'camiseta' => ['masculino › Camisetas', 'apparel_light', .450, false],
            'calca' => ['feminino › Calças Femininas', 'pants', .750, false],
            'tenis' => ['Calçados › Tenis', 'footwear', 1.200, false],
            'oculos' => ['optico › Gafas de sol', 'eyewear', .350, false],
            'vestido' => ['feminino › Vestidos', 'dress', .600, false],
            'vinho' => ['bebidas › Vinos', 'wine', 1.500, true],
            'perfume' => ['Perfumes › Perfumería de nicho', 'perfume', .500, true],
            'moveis' => ['casa › Muebles', 'furniture', 15.000, true],
        ];
    }
}
