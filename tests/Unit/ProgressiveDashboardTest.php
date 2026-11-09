<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProgressiveDashboardTest extends TestCase
{
    public function test_heavy_dashboard_sections_are_loaded_on_demand(): void
    {
        $root = dirname(__DIR__, 2);
        $hub = file_get_contents($root.'/resources/views/admin/dashboard/index.blade.php');
        $metrics = file_get_contents($root.'/resources/views/admin/dashboard/metrics.blade.php');
        $reports = file_get_contents($root.'/resources/views/admin/dashboard/reports.blade.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/Admin/DashboardController.php');

        foreach (['orders', 'integration', 'users', 'catalog', 'metrics', 'reports'] as $section) {
            $this->assertStringContainsString("admin.dashboard.{$section}", $hub);
        }
        $this->assertStringNotContainsString('data-dashboard-insights', $hub);
        $this->assertStringNotContainsString('admin.dashboard.countries', $hub);
        $this->assertStringContainsString('data-dashboard-insights', $metrics);
        $this->assertStringContainsString('admin.dashboard.insights', $metrics);
        $this->assertStringContainsString('admin.dashboard.countries', $reports);
        $this->assertStringContainsString('public function insights(Request $request)', $controller);
        $this->assertStringContainsString('public function countries(', $controller);
        $this->assertStringContainsString('dashboardAnalyticsDays', $controller);
        $this->assertStringContainsString('name="payment_status"', file_get_contents($root.'/resources/views/admin/dashboard/orders.blade.php'));
        $this->assertStringContainsString('name="verification"', file_get_contents($root.'/resources/views/admin/dashboard/users.blade.php'));
        $this->assertStringContainsString('name="stock"', file_get_contents($root.'/resources/views/admin/dashboard/catalog.blade.php'));
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
