<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminOrderReadStatusTest extends TestCase
{
    public function test_orders_expose_read_and_period_filters_and_are_marked_when_opened(): void
    {
        $controller = file_get_contents(__DIR__.'/../../app/Http/Controllers/Admin/OrderController.php');
        $model = file_get_contents(__DIR__.'/../../app/Models/Order.php');
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/orders/index.blade.php');

        $this->assertStringContainsString("get('read_status') === 'unread'", $controller);
        $this->assertStringContainsString("get('read_status') === 'read'", $controller);
        $this->assertStringContainsString("'today' => now()->startOfDay()", $controller);
        $this->assertStringContainsString("'7_days' => now()->subDays(6)->startOfDay()", $controller);
        $this->assertStringContainsString("'30_days' => now()->subDays(29)->startOfDay()", $controller);
        $this->assertStringContainsString("->whereNull('read_at')", $controller);
        $this->assertStringContainsString("->update(['read_at' => \$readAt])", $controller);
        $this->assertStringContainsString("'read_at' => 'datetime'", $model);
        $this->assertStringContainsString('name="read_status"', $view);
        $this->assertStringContainsString('name="period"', $view);
        $this->assertStringContainsString("{{ \$order->read_at ? 'Lido' : 'Não lido' }}", $view);
    }

    public function test_order_read_migrations_create_the_index_and_preserve_existing_history(): void
    {
        $schema = file_get_contents(__DIR__.'/../../database/migrations/2026_10_26_100000_add_read_status_to_orders_table.php');
        $backfill = file_get_contents(__DIR__.'/../../database/migrations/2026_10_26_100100_mark_existing_orders_as_read.php');

        $this->assertStringContainsString("timestamp('read_at')->nullable()", $schema);
        $this->assertStringContainsString("index(['read_at', 'created_at']", $schema);
        $this->assertStringContainsString("update(['read_at' => DB::raw('created_at')])", $backfill);
    }
}
