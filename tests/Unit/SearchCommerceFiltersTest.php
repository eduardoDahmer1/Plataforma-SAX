<?php

namespace Tests\Unit;

use App\Http\Controllers\SearchController;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SearchCommerceFiltersTest extends TestCase
{
    public function test_array_filters_are_normalized_deduplicated_and_limited(): void
    {
        $controller = (new ReflectionClass(SearchController::class))->newInstanceWithoutConstructor();
        $method = (new ReflectionClass($controller))->getMethod('requestedArray');
        $request = Request::create('/search', 'GET', [
            'sizes' => array_merge([' M ', 'L', 'M', ''], range(1, 40)),
        ]);

        $result = $method->invoke($controller, $request, 'sizes');

        $this->assertSame('M', $result[0]);
        $this->assertSame('L', $result[1]);
        $this->assertCount(30, $result);
        $this->assertSame($result, array_values(array_unique($result)));
    }

    public function test_search_and_catalogs_expose_contextual_realtime_commerce_filters(): void
    {
        $sidebar = file_get_contents(__DIR__.'/../../resources/views/components/sidebar-filters.blade.php');
        $facets = file_get_contents(__DIR__.'/../../resources/views/search/partials/dynamic-facets.blade.php');
        $runtime = file_get_contents(__DIR__.'/../../resources/views/search/partials/filter-runtime.blade.php');
        $catalog = file_get_contents(__DIR__.'/../../resources/views/catalog/show.blade.php');
        $controller = file_get_contents(__DIR__.'/../../app/Http/Controllers/SearchController.php');

        $this->assertStringContainsString('id="search-dynamic-facets"', $sidebar);
        $this->assertStringContainsString('id="price-range-min"', $facets);
        $this->assertStringContainsString('id="price-range-max"', $facets);
        $this->assertStringContainsString('name="sizes[]"', $facets);
        $this->assertStringContainsString('Tamanhos e medidas', $facets);
        $this->assertStringContainsString('name="colors[]"', $facets);
        $this->assertStringNotContainsString('name="stores[]"', $sidebar.$facets);
        $this->assertStringNotContainsString('Disponível em ', $sidebar.$facets);
        $this->assertStringContainsString('name="coupon"', $facets);
        $this->assertStringContainsString('data-remove-filter', $sidebar);
        $this->assertStringContainsString('data-filter-suggestion', $sidebar);
        $this->assertStringContainsString("params.append(el.name, value)", $runtime);
        $this->assertStringContainsString('dynamicFacets.innerHTML = data.facets', $runtime);
        $this->assertStringContainsString("@include('search.partials.filter-runtime'", $catalog);
        $this->assertStringContainsString('contextFilters', $catalog);
        $this->assertStringContainsString("currency['rate']", $controller);
        $this->assertStringContainsString('contextSuggestsVolume', $controller);
        $this->assertStringContainsString('applyCouponScope', $controller);
    }
}
