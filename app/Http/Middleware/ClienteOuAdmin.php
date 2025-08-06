<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClienteOuAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o usuário está autenticado e é cliente ou admin
        if (auth()->check() && (auth()->user()->hasRole('cliente') || auth()->user()->hasRole('admin'))) {
            return $next($request);
        }

        return redirect()->route('login');  // Redireciona se não for cliente ou admin
    }
}
