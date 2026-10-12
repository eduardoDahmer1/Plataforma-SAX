<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MaintenanceMiddlewareSafetyTest extends TestCase
{
    public function test_custom_middleware_preserves_laravel_native_maintenance_mode(): void
    {
        $middleware = file_get_contents(
            __DIR__.'/../../app/Http/Middleware/PreventRequestsDuringMaintenance.php'
        );

        $nativeCheck = strpos($middleware, '$this->app->isDownForMaintenance()');
        $databaseRead = strpos($middleware, 'SystemSetting::first()');

        $this->assertStringContainsString('extends FrameworkMaintenanceMiddleware', $middleware);
        $this->assertStringContainsString('return parent::handle($request, $next);', $middleware);
        $this->assertNotFalse($nativeCheck);
        $this->assertNotFalse($databaseRead);
        $this->assertLessThan($databaseRead, $nativeCheck);
    }
}
