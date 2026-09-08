<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\VisibleCatalogProductsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisibleCatalogProductsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_with_its_own_stock_is_visible(): void
    {
        $parent = $this->parent(['stock' => 2]);

        $this->assertSame([$parent->id], $this->visibleIds());
    }

    public function test_parent_without_stock_is_visible_when_an_active_child_has_stock(): void
    {
        $parent = $this->parent(['stock' => 0]);
        $this->child($parent, ['stock' => 3, 'status' => 1]);

        $this->assertSame([$parent->id], $this->visibleIds());
    }

    public function test_parent_without_stock_is_hidden_when_all_children_are_out_of_stock(): void
    {
        $parent = $this->parent(['stock' => 0]);
        $this->child($parent, ['stock' => 0]);
        $this->child($parent, ['stock' => 4, 'status' => 0]);

        $this->assertSame([], $this->visibleIds());
    }

    public function test_child_is_never_returned_as_a_catalog_card(): void
    {
        $parent = $this->parent(['stock' => 0]);
        $child = $this->child($parent, ['stock' => 2]);

        $this->assertSame([$parent->id], $this->visibleIds());
        $this->assertNotContains($child->id, $this->visibleIds());
    }

    public function test_brand_filter_can_be_applied_to_the_base_query(): void
    {
        $targetBrand = $this->brand('Marca objetivo');
        $otherBrand = $this->brand('Otra marca');
        $visibleParent = $this->parent(['brand_id' => $targetBrand->id, 'stock' => 1]);
        $this->parent(['brand_id' => $otherBrand->id, 'stock' => 1]);

        $ids = VisibleCatalogProductsService::builder()
            ->where('products.brand_id', $targetBrand->id)
            ->pluck('id')
            ->all();

        $this->assertSame([$visibleParent->id], $ids);
    }

    private function visibleIds(): array
    {
        return VisibleCatalogProductsService::builder()->orderBy('products.id')->pluck('id')->all();
    }

    private function brand(string $name): Brand
    {
        return Brand::create(['name' => $name, 'slug' => str($name)->slug(), 'status' => 1]);
    }

    private function parent(array $attributes = []): Product
    {
        return Product::create($attributes + $this->productAttributes([
            'product_role' => 'P',
            'parent_id' => null,
        ]));
    }

    private function child(Product $parent, array $attributes = []): Product
    {
        return Product::create($attributes + $this->productAttributes([
            'product_role' => 'F',
            'parent_id' => $parent->id,
            'photo' => null,
        ]));
    }

    private function productAttributes(array $attributes = []): array
    {
        $category = Category::firstOrCreate(
            ['slug' => 'categoria-visible'],
            ['name' => 'Categoría visible', 'status' => 1],
        );

        return $attributes + [
            'sku' => 'SKU-' . uniqid(),
            'name' => 'Producto de prueba',
            'price' => 10,
            'stock' => 0,
            'status' => 1,
            'is_outlet' => false,
            'category_id' => $category->id,
            'photo' => 'producto.jpg',
            'gallery' => [],
        ];
    }
}
