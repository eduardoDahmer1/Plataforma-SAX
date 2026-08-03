<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        $user = Auth::user();

        $routeName = $request->route()?->getName();

        if ($user && $user->canAccessAdminRoute($routeName)) {
            return $next($request);
        }

        if ($user?->isAdmin()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => __('messages.admin_access_denied'),
                ], 403);
            }

            $areaKey = Str::is([
                'admin.orders.*',
                'admin.clients.*',
                'admin.abandoned-carts.*',
            ], (string) $routeName)
                ? 'admin_area_sales'
                : 'admin_area_system';

            return redirect()
                ->route('admin.index')
                ->with('admin_access_restricted_area', $areaKey);
        }

        abort(403, __('messages.admin_access_denied'));
    }
}
