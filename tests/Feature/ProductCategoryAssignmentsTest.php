<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ProductControllerAdmin;
use App\Http\Controllers\CategoriasFilhasController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Models\CategoriasFilhas;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductCategoryAssignmentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::create('subcategories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id');
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });
        Schema::create('childcategories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable();
            $table->foreignId('subcategory_id');
            $table->string('name');
            $table->string('slug');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::create('brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('sku');
            $table->string('name')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->boolean('status')->default(true);
            $table->boolean('is_outlet')->default(false);
            $table->string('product_role')->default('P');
            $table->string('photo')->nullable();
            $table->foreignId('brand_id')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->foreignId('subcategory_id')->nullable();
            $table->foreignId('childcategory_id')->nullable();
            $table->foreignId('parent_id')->nullable();
            $table->timestamps();
        });
        Schema::create('product_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id');
            $table->string('locale');
            $table->string('name')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
        Schema::create('attributes', function (Blueprint $table): void {
            $table->id();
        });

        (require database_path('migrations/2026_09_17_120000_create_product_category_assignments_table.php'))->up();
        (require database_path('migrations/2026_09_18_090000_rename_product_category_assignments_table.php'))->up();
    }

    public function test_product_is_listed_once_in_its_primary_and_additional_taxonomies(): void
    {
        Cache::flush();

        [$primaryCategory, $primarySubcategory, $primaryChildcategory] = $this->taxonomy('principal');
        [$additionalCategory, $additionalSubcategory, $additionalChildcategory] = $this->taxonomy('adicional');

        $product = Product::withoutEvents(fn () => Product::create([
            'sku' => 'MULTI-CATEGORY-1',
            'name' => 'Produto multicategoria',
            'price' => 10,
            'stock' => 2,
            'status' => 1,
            'is_outlet' => false,
            'product_role' => 'P',
            'photo' => 'produto.jpg',
            'category_id' => $primaryCategory->id,
            'subcategory_id' => $primarySubcategory->id,
            'childcategory_id' => $primaryChildcategory->id,
        ]));

        $product->additionalCategories()->create([
            'category_id' => $additionalCategory->id,
            'subcategory_id' => $additionalSubcategory->id,
            'childcategory_id' => $additionalChildcategory->id,
        ]);
        $product->additionalCategories()->create([
            'category_id' => $additionalCategory->id,
            'subcategory_id' => $additionalSubcategory->id,
            'childcategory_id' => $additionalChildcategory->id,
        ]);

        $this->assertSame([$product->id], $this->categoryProductIds($primaryCategory));
        $this->assertSame([$product->id], $this->categoryProductIds($additionalCategory));
        $this->assertSame([$product->id], $this->subcategoryProductIds($primarySubcategory));
        $this->assertSame([$product->id], $this->subcategoryProductIds($additionalSubcategory));
        $this->assertSame([$product->id], $this->childcategoryProductIds($primaryChildcategory));
        $this->assertSame([$product->id], $this->childcategoryProductIds($additionalChildcategory));
    }

    public function test_deleting_an_assignment_keeps_the_primary_taxonomy_unchanged(): void
    {
        [$primaryCategory, $primarySubcategory, $primaryChildcategory] = $this->taxonomy('principal-delete');
        [$additionalCategory, $additionalSubcategory, $additionalChildcategory] = $this->taxonomy('adicional-delete');
        [$secondCategory, $secondSubcategory, $secondChildcategory] = $this->taxonomy('segunda-adicional');

        $product = Product::withoutEvents(fn () => Product::create([
            'sku' => 'MULTI-CATEGORY-2',
            'name' => 'Produto com remoção',
            'price' => 10,
            'stock' => 1,
            'status' => 1,
            'is_outlet' => false,
            'product_role' => 'P',
            'photo' => 'produto.jpg',
            'category_id' => $primaryCategory->id,
            'subcategory_id' => $primarySubcategory->id,
            'childcategory_id' => $primaryChildcategory->id,
        ]));

        $this->syncAssignments($product, [
            [
                'category_id' => $additionalCategory->id,
                'subcategory_id' => $additionalSubcategory->id,
                'childcategory_id' => $additionalChildcategory->id,
            ],
            [
                'category_id' => $secondCategory->id,
                'subcategory_id' => $secondSubcategory->id,
                'childcategory_id' => $secondChildcategory->id,
            ],
            ['category_id' => '', 'subcategory_id' => '', 'childcategory_id' => ''],
        ]);
        $this->assertCount(2, $product->fresh()->additionalCategories);

        $this->syncAssignments($product, [[
            'category_id' => $secondCategory->id,
            'subcategory_id' => $secondSubcategory->id,
            'childcategory_id' => $secondChildcategory->id,
        ]]);
        $product->refresh();

        $this->assertCount(1, $product->additionalCategories);
        $this->assertSame($secondCategory->id, $product->additionalCategories->first()->category_id);
        $this->assertSame($primaryCategory->id, $product->category_id);
        $this->assertSame($primarySubcategory->id, $product->subcategory_id);
        $this->assertSame($primaryChildcategory->id, $product->childcategory_id);
    }

    public function test_product_without_additional_categories_keeps_the_existing_catalog_behavior(): void
    {
        [$category, $subcategory, $childcategory] = $this->taxonomy('sem-adicional');
        $product = Product::withoutEvents(fn () => Product::create([
            'sku' => 'MULTI-CATEGORY-3',
            'name' => 'Produto sem categoria adicional',
            'price' => 10,
            'stock' => 1,
            'status' => 1,
            'is_outlet' => false,
            'product_role' => 'P',
            'photo' => 'produto.jpg',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'childcategory_id' => $childcategory->id,
        ]));

        $this->assertSame([$product->id], $this->categoryProductIds($category));
        $this->assertSame([$product->id], $this->subcategoryProductIds($subcategory));
        $this->assertSame([$product->id], $this->childcategoryProductIds($childcategory));
    }

    private function syncAssignments(Product $product, array $assignments): void
    {
        $method = new \ReflectionMethod(ProductControllerAdmin::class, 'syncAdditionalCategories');
        $method->invoke(app(ProductControllerAdmin::class), $product, $assignments);
    }

    private function taxonomy(string $suffix): array
    {
        $category = Category::withoutEvents(fn () => Category::create([
            'name' => "Categoria {$suffix}",
            'slug' => "categoria-{$suffix}",
            'status' => 1,
        ]));
        $subcategory = Subcategory::withoutEvents(fn () => Subcategory::create([
            'name' => "Subcategoria {$suffix}",
            'slug' => "subcategoria-{$suffix}",
            'category_id' => $category->id,
        ]));
        $childcategory = CategoriasFilhas::withoutEvents(fn () => CategoriasFilhas::create([
            'name' => "Categoria filha {$suffix}",
            'slug' => "categoria-filha-{$suffix}",
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
        ]));

        return [$category, $subcategory, $childcategory];
    }

    private function categoryProductIds(Category $category): array
    {
        $view = app(CategoryController::class)->show(Request::create('/categorias/'.$category->slug), $category->slug);

        return $view->getData()['products']->pluck('id')->all();
    }

    private function subcategoryProductIds(Subcategory $subcategory): array
    {
        $view = app(SubcategoryController::class)->show(Request::create('/subcategorias/'.$subcategory->slug), $subcategory->slug);

        return $view->getData()['products']->pluck('id')->all();
    }

    private function childcategoryProductIds(CategoriasFilhas $childcategory): array
    {
        $view = app(CategoriasFilhasController::class)->show(Request::create('/categorias-filhas/'.$childcategory->slug), $childcategory->slug);

        return $view->getData()['products']->pluck('id')->all();
    }
}
