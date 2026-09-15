<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProgressiveDashboardTest extends TestCase
{
    public function test_heavy_dashboard_sections_are_loaded_on_demand(): void
    {
        $root = dirname(__DIR__, 2);
        $view = file_get_contents($root.'/resources/views/admin/dashboard/index.blade.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/DashboardController.php');

        $this->assertStringContainsString('data-dashboard-insights', $view);
        $this->assertStringContainsString('admin.dashboard.insights', $view);
        $this->assertStringContainsString('admin.dashboard.countries', $view);
        $this->assertStringNotContainsString("selectedReport['visitors_by_country']->isNotEmpty", $view);
        $this->assertStringContainsString('public function insights()', $controller);
        $this->assertStringContainsString('public function countries(', $controller);
    }

    public function test_screen_dimensions_are_collected_end_to_end(): void
    {
        $root = dirname(__DIR__, 2);
        $tracker = file_get_contents($root.'/public/js/site-analytics.js');
        $endpoint = file_get_contents($root.'/app/Http/Controllers/SiteAnalyticsController.php');
        $model = file_get_contents($root.'/app/Models/SiteAnalyticsEvent.php');

        foreach (['screen_width', 'screen_height', 'viewport_width', 'viewport_height'] as $field) {
            $this->assertStringContainsString($field, $tracker);
            $this->assertStringContainsString($field, $endpoint);
            $this->assertStringContainsString($field, $model);
        }
    }
}
