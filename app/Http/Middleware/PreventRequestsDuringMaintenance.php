<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\SystemSetting;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as FrameworkMaintenanceMiddleware;
use Illuminate\Http\Request;

class PreventRequestsDuringMaintenance extends FrameworkMaintenanceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Respeita primeiro o modo nativo acionado por `php artisan down`.
        // Esta verificação usa o arquivo local de manutenção e não consulta o
        // banco, portanto continua funcionando mesmo sob sobrecarga do MySQL.
        if ($this->app->isDownForMaintenance()) {
            return parent::handle($request, $next);
        }

        $user = auth()->user();

        // Admin sempre vê tudo
        if ($user && $user->isAdmin()) {
            return $next($request);
        }
    
        // Rotas liberadas mesmo em manutenção
        $except = ['login', 'register', 'password/*', 'manutencao', 'admin', 'admin/*'];
        foreach ($except as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // O formulário de login é um modal da home. Durante a manutenção,
        // libera somente a home quando ela foi aberta explicitamente para login.
        if ($request->is('/') && $request->query('open') === 'login') {
            return $next($request);
        }

        $setting = SystemSetting::first();

        // Redireciona pra manutenção apenas se maintenance = 1
        if ((int) ($setting?->getRawOriginal('maintenance') ?? 0) === 1) {
            return redirect()->route('maintenance.page');
        }
    
        return $next($request);
    }
}
