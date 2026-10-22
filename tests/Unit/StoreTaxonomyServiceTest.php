<?php

namespace Tests\Unit;

use App\Models\CategoriasFilhas;
use App\Models\Category;
use App\Models\Subcategory;
use App\Services\StoreControlService;
use App\Services\StoreTaxonomyService;
use Tests\TestCase;

class StoreTaxonomyServiceTest extends TestCase
{
    public function test_optical_profile_restricts_each_taxonomy_level_to_the_optical_tree(): void
    {
        $service = new StoreTaxonomyService($this->storeControl(true));

        $categorySql = str_replace(['`', '"'], '', $service->categories(Category::query())->toSql());
        $subcategorySql = $service->subcategories(Subcategory::query())->toSql();
        $childCategorySql = $service->childCategories(CategoriasFilhas::query())->toSql();

        $this->assertStringContainsString('categories.ref_code', strtolower($categorySql));
        $this->assertStringContainsString('lower(categories.slug)', strtolower($categorySql));
        $this->assertStringNotContainsString(' like ', strtolower($categorySql));
        $this->assertStringContainsString('exists', strtolower($subcategorySql));
        $this->assertStringContainsString('categories', strtolower($subcategorySql));
        $this->assertStringContainsString('subcategories', strtolower($childCategorySql));
        $this->assertStringContainsString('categories', strtolower($childCategorySql));
    }

    public function test_sax_profile_hides_categories_excluded_from_the_storefront(): void
    {
        config()->set('catalog-sync.hidden_category_slugs', [
            'chocolate', 'electronicos', 'cavalheiro', 'cavalo',
        ]);
        $service = new StoreTaxonomyService($this->storeControl(false));
        $query = Category::query();

        $this->assertSame($query, $service->categories($query));
        $this->assertStringContainsString('lower(categories.slug)', strtolower($query->toSql()));
        $this->assertStringContainsString('not in', strtolower($query->toSql()));
        $this->assertSame(
            ['chocolate', 'electronicos', 'cavalheiro', 'cavalo'],
            $query->getBindings(),
        );
    }

    private function storeControl(bool $isOtica): StoreControlService
    {
        return new class($isOtica) extends StoreControlService
        {
            public function __construct(private readonly bool $optical)
            {
            }

            public function isOtica(): bool
            {
                return $this->optical;
            }
        };
    }
}
