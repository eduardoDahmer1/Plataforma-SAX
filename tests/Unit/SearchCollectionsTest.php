<?php

namespace Tests\Unit;

use App\Http\Controllers\SearchController;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SearchCollectionsTest extends TestCase
{
    public function test_collections_have_the_expected_default_sorting(): void
    {
        $controller = (new ReflectionClass(SearchController::class))->newInstanceWithoutConstructor();
        $method = (new ReflectionClass($controller))->getMethod('requestedSort');

        $newArrivals = Request::create('/colecoes/new-arrivals', 'GET', ['collection' => 'new-arrivals']);
        $trending = Request::create('/colecoes/trending', 'GET', ['collection' => 'trending']);

        $this->assertSame('latest', $method->invoke($controller, $newArrivals));
        $this->assertSame('trending', $method->invoke($controller, $trending));
    }

    public function test_trending_orders_by_views_with_a_stable_tiebreaker(): void
    {
        $controller = (new ReflectionClass(SearchController::class))->newInstanceWithoutConstructor();
        $method = (new ReflectionClass($controller))->getMethod('applySorting');
        $query = new class
        {
            public array $orders = [];

            public function orderByDesc(string $column): self
            {
                $this->orders[] = [$column, 'desc'];

                return $this;
            }
        };

        $method->invoke($controller, $query, 'trending');

        $this->assertSame([
            ['products.views', 'desc'],
            ['products.id', 'desc'],
        ], $query->orders);
    }
}
