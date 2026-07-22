<?php

namespace Tests\Unit;

use App\Services\DailyMostViewedProducts;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class DailyMostViewedProductsTest extends TestCase
{
    public function test_selection_is_stable_during_the_same_day(): void
    {
        $ids = range(1, 60);
        $morning = CarbonImmutable::parse('2026-07-21 08:00:00');
        $evening = CarbonImmutable::parse('2026-07-21 22:00:00');

        $this->assertSame(
            DailyMostViewedProducts::rotateIds($ids, $morning, 12)->all(),
            DailyMostViewedProducts::rotateIds($ids, $evening, 12)->all()
        );
    }

    public function test_all_products_change_on_the_next_day_when_pool_is_large_enough(): void
    {
        $ids = range(1, 60);
        $today = CarbonImmutable::parse('2026-07-21');
        $tomorrow = $today->addDay();

        $todayIds = DailyMostViewedProducts::rotateIds($ids, $today, 12);
        $tomorrowIds = DailyMostViewedProducts::rotateIds($ids, $tomorrow, 12);

        $this->assertCount(12, $todayIds);
        $this->assertCount(12, $tomorrowIds);
        $this->assertCount(0, $todayIds->intersect($tomorrowIds));
    }

    public function test_it_falls_back_gracefully_when_catalog_is_small(): void
    {
        $selection = DailyMostViewedProducts::rotateIds(range(1, 8), CarbonImmutable::parse('2026-07-21'), 12);

        $this->assertCount(8, $selection);
        $this->assertCount(8, $selection->unique());
    }
}
