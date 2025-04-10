<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    /**
     * Manipulate a request and determine if the user is an admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verifica se o usuário está autenticado e se é do tipo Admin
        if (Auth::check() && Auth::user()->user_type == 1) {
            return $next($request);
        }

        // Se o usuário não for Admin, redireciona para a página inicial
        return redirect('/');
    }
}
