<?php

namespace Tests\Unit;

use App\Http\Controllers\SearchController;
use App\Services\StorefrontLayoutService;
use Illuminate\Http\Request;
use ReflectionClass;
use Tests\TestCase;

class OpticalSearchFiltersTest extends TestCase
{
    public function test_optical_search_ignores_a_category_left_in_the_url(): void
    {
        $this->mock(StorefrontLayoutService::class)
            ->shouldReceive('effective')->once()->andReturn('vista');

        $request = Request::create('/search', 'GET', [
            'category' => '123',
            'subcategory' => '456',
            'categoriasfilhas' => '789',
        ]);

        $this->normalizeFilters($request);

        $this->assertFalse($request->has('category'));
        $this->assertSame('456', $request->query('subcategory'));
        $this->assertSame('789', $request->query('categoriasfilhas'));
    }

    public function test_sax_search_preserves_the_category_filter(): void
    {
        $this->mock(StorefrontLayoutService::class)
            ->shouldReceive('effective')->once()->andReturn('sax');

        $request = Request::create('/search', 'GET', ['category' => '123']);

        $this->normalizeFilters($request);

        $this->assertSame('123', $request->query('category'));
    }

    public function test_sidebar_only_renders_category_selector_outside_optical_layout(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/components/sidebar-filters.blade.php');

        $this->assertStringContainsString('@unless($isOpticalLayout)', $view);
        $this->assertStringContainsString("'name' => 'subcategory'", $view);
        $this->assertStringContainsString("'name' => 'categoriasfilhas'", $view);
    }

    private function normalizeFilters(Request $request): void
    {
        $controller = (new ReflectionClass(SearchController::class))->newInstanceWithoutConstructor();
        (new ReflectionClass(SearchController::class))
            ->getMethod('removeOpticalCategoryFilter')
            ->invoke($controller, $request);
    }
}
