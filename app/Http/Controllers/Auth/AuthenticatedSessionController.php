<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('home', ['open' => 'login']);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: __('auth.failed'),
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }

        $request->session()->regenerate();

        // Restaurar carrinho do usuário logado
        $order = Order::where('user_id', auth()->id())
            ->where('status', 'cart')
            ->with(['items.product'])
            ->first();

        $cart = [];
        if ($order) {
            foreach ($order->items as $item) {
                $cart[$item->product_id] = [
                    'product_id'    => $item->product_id,
                    'quantity'      => $item->quantity,
                    'price'         => $item->price,
                    'name'          => $item->product->name ?? $item->name,
                    'external_name' => $item->product->external_name ?? $item->external_name,
                    'slug'          => $item->product->slug ?? $item->slug,
                    'sku'           => $item->product->sku ?? $item->sku,
                    'stock'         => $item->product->stock ?? 0,
                ];
            }
        }

        session()->put('cart', $cart);

        if ($request->expectsJson()) {
            $redirect = session()->pull('url.intended')
                ?? $request->input('redirect_to')
                ?? RouteServiceProvider::HOME;

            return response()->json([
                'success'  => true,
                'redirect' => $redirect,
            ]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Handle actions after authentication.
     */
    public function authenticated(Request $request, $user)
    {
        // Só redireciona direto para o dashboard
        return redirect()->intended('user/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
