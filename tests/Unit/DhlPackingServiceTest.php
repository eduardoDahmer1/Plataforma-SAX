<?php

namespace Tests\Unit;

use App\Models\DhlPackage;
use App\Models\Product;
use App\Services\Dhl\DhlPackingService;
use App\Services\Dhl\DhlProductMeasurementEstimator;
use App\Services\Dhl\DhlSettingsService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DhlPackingServiceTest extends TestCase
{
    public function test_it_keeps_small_accessories_in_one_small_box(): void
    {
        $result = $this->service()->pack(collect([
            $this->cartItem('Carteira de couro', 'accessory', 3),
        ]), $this->packages());

        $this->assertCount(1, $result['packages']);
        $this->assertSame('small', $result['packages'][0]['code']);
        $this->assertSame(3, $result['packages'][0]['item_count']);
        $this->assertSame(1.188, $result['packages'][0]['billable_weight']);
        $this->assertTrue($result['uses_estimates']);
    }

    public function test_it_uses_the_specific_kids_tshirt_average_instead_of_the_generic_category_average(): void
    {
        $product = new Product([
            'sku' => 'KIDS-TEE-1',
            'external_name' => 'Camiseta infantil de algodão',
        ]);

        $measurement = (new DhlProductMeasurementEstimator())->forProduct($product);

        $this->assertSame('kids_tshirt', $measurement['profile']);
        $this->assertSame(0.2, $measurement['weight']);
        $this->assertSame(28.0, $measurement['length']);
        $this->assertSame(22.0, $measurement['width']);
        $this->assertSame(3.0, $measurement['height']);
        $this->assertTrue($measurement['estimated']);
    }

    public function test_three_kids_tshirts_are_packed_with_six_hundred_grams_of_content(): void
    {
        $box3 = collect([
            new DhlPackage([
                'code' => '3BX', 'name' => 'DHL Box 3',
                'length_cm' => 33.7, 'width_cm' => 32.2, 'height_cm' => 9.2,
                'tare_weight_kg' => .24, 'max_gross_weight_kg' => 2,
                'max_items' => 5, 'fill_ratio_percent' => 70, 'sort_order' => 10,
            ]),
        ]);

        $result = $this->service()->pack(collect([
            $this->cartItem('Camiseta infantil de algodão', null, 3),
        ]), $box3);

        $this->assertCount(1, $result['packages']);
        $this->assertSame('3BX', $result['packages'][0]['code']);
        $this->assertSame(3, $result['packages'][0]['item_count']);
        $this->assertSame(0.84, $result['actual_weight']);
        $this->assertSame(1.997, $result['billable_weight']);
    }

    public function test_it_upgrades_to_a_large_box_when_that_avoids_two_medium_boxes(): void
    {
        $result = $this->service()->pack(collect([
            $this->cartItem('Tênis', 'footwear', 2),
        ]), $this->packages());

        $this->assertCount(1, $result['packages']);
        $this->assertSame('large', $result['packages'][0]['code']);
        $this->assertSame(2, $result['packages'][0]['item_count']);
    }

    public function test_it_opens_another_volume_when_the_large_box_limit_is_reached(): void
    {
        $result = $this->service()->pack(collect([
            $this->cartItem('Camiseta', 'apparel_light', 11),
        ]), $this->packages());

        $this->assertCount(2, $result['packages']);
        $this->assertSame(11, $result['item_count']);
    }

    public function test_it_quotes_review_products_with_estimated_measurements(): void
    {
        $result = $this->service()->pack(collect([
            $this->cartItem('Perfume Eau de Parfum', null, 1),
        ]), $this->packages());

        $this->assertCount(1, $result['packages']);
        $this->assertTrue($result['requires_manual_review']);
        $this->assertSame('perfume', $result['review_items'][0]['profile']);
        $this->assertGreaterThan(0, $result['actual_weight']);
    }

    public function test_it_explains_when_estimates_are_disabled_for_a_product_without_measurements(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ative os perfis estimados da DHL');

        $this->service(false)->pack(collect([
            $this->cartItem('Sandália Valentino', null, 1),
        ]), $this->packages());
    }

    public function test_it_uses_a_custom_piece_when_a_product_does_not_fit_a_registered_box(): void
    {
        $product = new Product([
            'sku' => 'FURNITURE-1',
            'external_name' => 'Sofá grande',
            'shipping_profile' => 'furniture',
        ]);
        $product->id = 999001;

        $result = $this->service()->pack(collect([(object) [
            'product_id' => $product->id,
            'product' => $product,
            'quantity' => 1,
        ]]), $this->packages());

        $this->assertSame('custom', $result['packages'][0]['code']);
        $this->assertSame('Volume sob medida', $result['packages'][0]['name']);
        $this->assertSame(15.0, $result['packages'][0]['weight']);
        $this->assertTrue($result['requires_manual_review']);
    }

    private function service(bool $allowFallback = true): DhlPackingService
    {
        return new DhlPackingService(
            new DhlProductMeasurementEstimator(),
            new DhlSettingsService(),
            ['fallback_measurements_enabled' => $allowFallback, 'volumetric_divisor' => 5000],
        );
    }

    private function cartItem(string $name, ?string $profile, int $quantity): object
    {
        $product = new Product([
            'sku' => 'SKU-'.md5($name),
            'external_name' => $name,
            'shipping_profile' => $profile,
        ]);
        $product->id = random_int(1, 999999);

        return (object) [
            'product_id' => $product->id,
            'product' => $product,
            'quantity' => $quantity,
        ];
    }

    private function packages()
    {
        return collect([
            new DhlPackage(['code' => 'small', 'name' => 'Pequena', 'length_cm' => 33, 'width_cm' => 18, 'height_cm' => 10, 'tare_weight_kg' => .15, 'max_gross_weight_kg' => 1.2, 'max_items' => 3, 'fill_ratio_percent' => 70, 'sort_order' => 10]),
            new DhlPackage(['code' => 'medium', 'name' => 'Média', 'length_cm' => 33, 'width_cm' => 32, 'height_cm' => 18, 'tare_weight_kg' => .32, 'max_gross_weight_kg' => 5, 'max_items' => 6, 'fill_ratio_percent' => 70, 'sort_order' => 20]),
            new DhlPackage(['code' => 'large', 'name' => 'Grande', 'length_cm' => 33, 'width_cm' => 32, 'height_cm' => 34, 'tare_weight_kg' => .77, 'max_gross_weight_kg' => 10, 'max_items' => 10, 'fill_ratio_percent' => 70, 'sort_order' => 30]),
        ]);
    }
}
