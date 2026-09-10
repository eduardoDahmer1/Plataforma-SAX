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

        $categorySql = $service->categories(Category::query())->toSql();
        $subcategorySql = $service->subcategories(Subcategory::query())->toSql();
        $childCategorySql = $service->childCategories(CategoriasFilhas::query())->toSql();

        $this->assertStringContainsString('lower(categories.name)', strtolower($categorySql));
        $this->assertStringContainsString('exists', strtolower($subcategorySql));
        $this->assertStringContainsString('categories', strtolower($subcategorySql));
        $this->assertStringContainsString('subcategories', strtolower($childCategorySql));
        $this->assertStringContainsString('categories', strtolower($childCategorySql));
    }

    public function test_other_profiles_keep_the_original_query_unchanged(): void
    {
        $service = new StoreTaxonomyService($this->storeControl(false));
        $query = Category::query();

        $this->assertSame($query, $service->categories($query));
        $this->assertStringContainsString('categories', $query->toSql());
        $this->assertStringNotContainsString('lower(', strtolower($query->toSql()));
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
