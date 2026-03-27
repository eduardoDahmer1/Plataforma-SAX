<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Exibe a tela de registro.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Processa a requisição de registro.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', 'min:5'],
        ]);
    
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    
        // Dispara o evento de registro (envio de e-mail de verificação)
        // O try/catch evita que o cadastro trave se o servidor de e-mail oscilar
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::error('Falha ao disparar evento Registered: '.$e->getMessage(), [
                'user_id' => $user->id,
                'email'   => $user->email
            ]);
        }
    
        Auth::login($user);
    
        return redirect(RouteServiceProvider::HOME);
    }    
}