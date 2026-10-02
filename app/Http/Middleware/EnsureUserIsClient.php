<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user?->canShop()) {
            return $next($request);
        }

        if (! $user) {
            return redirect()->guest(route('login'))
                ->with('error', 'Você precisa estar logado como cliente para comprar.');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Carrinho e checkout estão disponíveis somente para clientes.',
            ], 403);
        }

        return redirect()
            ->route($user->isAdmin() ? 'admin.index' : 'home')
            ->with('error', 'Carrinho e checkout estão disponíveis somente para clientes.');
    }
}
