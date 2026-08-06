<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class PreventRequestsDuringMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $setting = SystemSetting::first();
    
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
    
        // Redireciona pra manutenção apenas se maintenance = 1
        if ((int) ($setting?->getRawOriginal('maintenance') ?? 0) === 1) {
            return redirect()->route('maintenance.page');
        }
    
        return $next($request);
    }
}
