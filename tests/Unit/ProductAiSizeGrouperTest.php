<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\ProductAiSizeGrouper;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ProductAiSizeGrouperTest extends TestCase
{
    public function test_same_brand_reference_and_color_are_grouped_ignoring_size(): void
    {
        $groups = (new ProductAiSizeGrouper)->group(new Collection([
            $this->product(11, 'SKU-S', 'MARCA MODELO AB12345 #S *001', 8),
            $this->product(12, 'SKU-M', 'MARCA MODELO AB12345 #M *001', 8),
            $this->product(13, 'SKU-L', 'MARCA MODELO AB12345 #L *001', 8),
        ]));

        $this->assertCount(1, $groups);
        $this->assertSame(11, $groups[0]['representative_id']);
        $this->assertSame([11, 12, 13], $groups[0]['product_ids']);
    }

    public function test_different_colors_or_brands_remain_independent(): void
    {
        $groups = (new ProductAiSizeGrouper)->group(new Collection([
            $this->product(21, 'SKU-1', 'MARCA MODELO AB12345 #S *001', 8),
            $this->product(22, 'SKU-2', 'MARCA MODELO AB12345 #M *002', 8),
            $this->product(23, 'SKU-3', 'MARCA MODELO AB12345 #L *001', 9),
        ]));

        $this->assertCount(3, $groups);
    }

    public function test_products_without_an_explicit_size_are_not_grouped_by_name(): void
    {
        $groups = (new ProductAiSizeGrouper)->group(new Collection([
            $this->product(31, 'SKU-1', 'MARCA MODELO AB12345 *001', 8),
            $this->product(32, 'SKU-2', 'MARCA MODELO AB12345 *001', 8),
        ]));

        $this->assertCount(2, $groups);
    }

    private function product(int $id, string $sku, string $externalName, int $brandId): Product
    {
        $product = new Product([
            'sku' => $sku,
            'external_name' => $externalName,
            'brand_id' => $brandId,
        ]);
        $product->id = $id;

        return $product;
    }
}
