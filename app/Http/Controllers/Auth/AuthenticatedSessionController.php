<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
    public function store(Request $request): RedirectResponse
    {
        // já vem com a lógica de autenticação
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }
    
        $request->session()->regenerate();
    
        // Restaurar carrinho da base
        $order = Order::where('user_id', auth()->id())
            ->where('status', 'cart')
            ->with('items') // garantir que puxe os itens
            ->first();
    
        $cart = [];
    
        if ($order) {
            foreach ($order->items as $item) {
                $cart[$item->product_id] = [
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    // você pode puxar nome, imagem etc. se quiser também
                ];
            }
        }
    
        session()->put('cart', $cart);
    
        return redirect()->intended(RouteServiceProvider::HOME);
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
