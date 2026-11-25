<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductAiBatch;
use App\Models\ProductAiBatchItem;
use App\Services\ProductCatalogReadiness;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCatalogReadinessTest extends TestCase
{
    public function test_complete_product_reaches_one_hundred_percent(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/ready.webp', 'image');

        $readiness = app(ProductCatalogReadiness::class)->product($this->completeProduct());

        $this->assertSame(100, $readiness['percentage']);
        $this->assertTrue($readiness['complete']);
        $this->assertSame([], $readiness['missing']);
    }

    public function test_missing_catalog_data_reduces_percentage_and_lists_reasons(): void
    {
        Storage::fake('public');
        $product = $this->completeProduct();
        $product->description = '<p><br></p>';
        $product->photo = null;
        $product->subcategory_id = null;
        $product->childcategory_id = null;

        $readiness = app(ProductCatalogReadiness::class)->product($product);

        $this->assertSame(50, $readiness['percentage']);
        $this->assertFalse($readiness['complete']);
        $this->assertSame([
            'Descripción',
            'Fotografía',
            'Categoría, subcategoría y categoría hija',
        ], $readiness['missing']);
    }

    public function test_batch_percentage_is_the_average_of_its_products(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/ready.webp', 'image');
        $complete = $this->completeProduct();
        $incomplete = $this->completeProduct();
        $incomplete->description = null;
        $incomplete->photo = null;
        $incomplete->subcategory_id = null;
        $incomplete->childcategory_id = null;

        $batch = ProductAiBatch::make();
        $batch->setRelation('items', new Collection([
            $this->itemFor($complete),
            $this->itemFor($incomplete),
        ]));

        $readiness = app(ProductCatalogReadiness::class)->batch($batch);

        $this->assertSame(75, $readiness['percentage']);
        $this->assertSame(1, $readiness['complete_products']);
        $this->assertSame(2, $readiness['total_products']);
    }

    private function completeProduct(): Product
    {
        return Product::make([
            'sku' => 'READY-1',
            'name' => 'Producto listo',
            'description' => 'Descripción completa',
            'photo' => 'products/ready.webp',
            'gallery' => [],
            'brand_id' => 1,
            'category_id' => 2,
            'subcategory_id' => 3,
            'childcategory_id' => 4,
            'price' => 10,
            'stock' => 0,
        ]);
    }

    private function itemFor(Product $product): ProductAiBatchItem
    {
        $item = ProductAiBatchItem::make();
        $item->setRelation('product', $product);

        return $item;
    }
}
