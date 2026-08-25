<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductAiPreparation;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductAiPreparationStatusTest extends TestCase
{
    public function test_product_without_preparation_is_pending(): void
    {
        $product = $this->productWithPreparation(null);

        $this->assertSame('pending', $product->productAiDisplayStatus());
    }

    public function test_generated_product_remains_pending_until_approved(): void
    {
        $product = $this->productWithPreparation(ProductAiPreparation::STATUS_GENERATED);

        $this->assertSame('pending', $product->productAiDisplayStatus());
    }

    public function test_completed_product_is_yellow_without_photo_and_green_with_photo(): void
    {
        Storage::fake('public');
        $product = $this->productWithPreparation(ProductAiPreparation::STATUS_COMPLETED);

        $this->assertSame('missing_photo', $product->productAiDisplayStatus());

        Storage::disk('public')->put('products/example.webp', 'image');
        $product->photo = 'products/example.webp';

        $this->assertSame('prepared', $product->productAiDisplayStatus());
    }

    public function test_not_found_and_failed_products_require_review(): void
    {
        $this->assertSame('review', $this->productWithPreparation(ProductAiPreparation::STATUS_NOT_FOUND)->productAiDisplayStatus());
        $this->assertSame('review', $this->productWithPreparation(ProductAiPreparation::STATUS_FAILED)->productAiDisplayStatus());
    }

    private function productWithPreparation(?string $status): Product
    {
        $product = Product::make(['price' => 10, 'photo' => null, 'gallery' => []]);
        $product->setRelation(
            'aiPreparation',
            $status ? ProductAiPreparation::make(['status' => $status]) : null,
        );

        return $product;
    }
}
