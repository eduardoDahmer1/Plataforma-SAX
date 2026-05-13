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
use Illuminate\Support\Facades\Validator;
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
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255', 'unique:'.User::class],
            'document' => ['required', 'string', 'max:50'],
            'phone_country' => ['required', 'string', 'in:55,595'],
            'phone_number' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', 'min:5'],
        ], [
            'email.email' => 'Informe um e-mail valido.',
            'email.unique' => 'Este e-mail ja esta cadastrado.',
            'phone_country.in' => 'Selecione um codigo de pais valido.',
            'password.confirmed' => 'A confirmacao da senha nao confere.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('auth_modal', 'register');
        }

        $validated = $validator->validated();
    
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'document' => $validated['document'],
            'phone_country' => str_replace('+', '', $validated['phone_country']),
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
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
    
        return redirect()
            ->route('user.profile.edit')
            ->with('success', 'Conta criada com sucesso. Complete seus dados de endereço para agilizar seus pedidos.');
    }    
}
