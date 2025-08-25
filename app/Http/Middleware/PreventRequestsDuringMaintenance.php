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
        $setting = SystemSetting::firstOrFail(); // garante que existe
    
        // Admin sempre vê tudo
        if ($user && $user->user_type == 1) {
            return $next($request);
        }
    
        // Rotas liberadas mesmo em manutenção
        $except = ['login', 'register', 'password/*', 'manutencao', 'admin/login'];
        foreach ($except as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }
    
        // Redireciona pra manutenção apenas se maintenance = 1
        if ($setting->maintenance == 1) {
            return redirect()->route('maintenance.page');
        }
    
        return $next($request);
    }
}
