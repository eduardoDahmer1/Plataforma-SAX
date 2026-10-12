<?php

namespace Tests\Unit;

use App\Http\Middleware\PreventRequestsDuringMaintenance;
use PHPUnit\Framework\TestCase;

class MaintenanceMiddlewareSafetyTest extends TestCase
{
    public function test_custom_middleware_preserves_laravel_native_maintenance_mode(): void
    {
        // Carrega de fato a classe contra a versão instalada do framework.
        // Isso detecta imediatamente assinaturas incompatíveis com a classe-pai.
        $this->assertTrue(class_exists(PreventRequestsDuringMaintenance::class));

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
