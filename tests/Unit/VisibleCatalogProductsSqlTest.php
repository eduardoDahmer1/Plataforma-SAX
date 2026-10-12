<?php

namespace Tests\Unit;

use App\Services\VisibleCatalogProductsService;
use Tests\TestCase;

class VisibleCatalogProductsSqlTest extends TestCase
{
    public function test_catalog_availability_uses_correlated_exists_instead_of_union(): void
    {
        $sql = str_replace(['`', '"'], '', strtolower(VisibleCatalogProductsService::builder()->toSql()));

        $this->assertStringNotContainsString(' union ', $sql);
        $this->assertStringContainsString('exists (select 1 from products as available_children', $sql);
        $this->assertStringContainsString('available_children.parent_id = products.id', $sql);
    }
}
