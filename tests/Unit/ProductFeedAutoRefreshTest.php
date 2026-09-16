<?php

namespace Tests\Unit;

use App\Services\ProductFeedRefreshService;
use App\Services\ProductFeedService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductFeedAutoRefreshTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::clear();
    }

    public function test_product_lifecycle_marks_the_feed_for_refresh(): void
    {
        $root = dirname(__DIR__, 2);
        $observer = file_get_contents($root.'/app/Observers/ProductObserver.php');

        $this->assertStringContainsString('public function created(', $observer);
        $this->assertStringContainsString('public function updated(', $observer);
        $this->assertStringContainsString('public function deleted(', $observer);
        $this->assertGreaterThanOrEqual(3, substr_count($observer, 'feedRefresh->markDirty()'));
        $this->assertStringContainsString("'status', 'stock', 'price', 'is_outlet'", $observer);
    }

    public function test_bulk_status_flows_and_scheduler_have_a_safe_fallback(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/ProductControllerAdmin.php');
        $kernel = file_get_contents($root.'/app/Console/Kernel.php');
        $refresh = file_get_contents($root.'/app/Services/ProductFeedRefreshService.php');
        $provider = file_get_contents($root.'/app/Providers/EventServiceProvider.php');

        $this->assertStringContainsString('updateOutlet(Request $request, ProductFeedRefreshService $feedRefresh)', $controller);
        $this->assertStringContainsString('revalidateStatus(ProductFeedRefreshService $feedRefresh)', $controller);
        $this->assertStringContainsString("command('products:refresh-feed')->everyMinute()", $kernel);
        $this->assertStringContainsString("command('products:refresh-feed --force')->hourly()", $kernel);
        $this->assertStringContainsString('Cache::lock(', $refresh);
        $this->assertStringContainsString('app()->terminating(', $refresh);
        $this->assertStringContainsString('Subcategory::observe(CatalogFeedTaxonomyObserver::class)', $provider);
        $this->assertStringContainsString('CategoriasFilhas::observe(CatalogFeedTaxonomyObserver::class)', $provider);
    }

    public function test_many_pending_marks_are_consolidated_into_one_generation(): void
    {
        $feed = $this->createMock(ProductFeedService::class);
        $feed->expects($this->once())->method('generate')->willReturn(['count' => 10]);
        $refresh = new ProductFeedRefreshService($feed);

        $refresh->markDirty();
        $refresh->markDirty();

        $this->assertTrue($refresh->isPending());
        $this->assertTrue($refresh->refreshPending());
        $this->assertFalse($refresh->isPending());
        $this->assertFalse($refresh->refreshPending());
    }
}
