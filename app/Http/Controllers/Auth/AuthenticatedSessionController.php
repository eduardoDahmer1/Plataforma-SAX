<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.failed')
                ], 401);
            }

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
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
            return response()->json([
                'success' => true,
                'redirect' => route('home')
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
