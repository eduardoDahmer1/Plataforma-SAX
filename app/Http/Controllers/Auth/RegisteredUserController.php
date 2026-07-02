<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
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
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
            'document' => trim((string) $request->input('document')),
            'phone_country' => preg_replace('/\D/', '', (string) $request->input('phone_country')),
            'phone_number' => trim((string) $request->input('phone_number')),
        ]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255', 'unique:'.User::class],
            'document' => ['required', 'string', 'regex:/^[A-Za-z0-9.\/\-\s]{5,30}$/'],
            'phone_country' => ['required', 'string', 'in:55,595'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9\s()+\-]{7,20}$/'],
            'password' => ['required', 'confirmed', 'min:8', 'max:72', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ], [
            'name.required' => 'Informe seu nome completo.',
            'name.min' => 'Seu nome deve ter pelo menos :min caracteres.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail valido.',
            'email.unique' => 'Este e-mail ja esta cadastrado.',
            'document.required' => 'Informe seu documento.',
            'phone_country.in' => 'Selecione um codigo de pais valido.',
            'phone_country.required' => 'Selecione o codigo do pais.',
            'phone_number.required' => 'Informe seu telefone.',
            'phone_number.regex' => 'Informe um telefone valido, sem letras.',
            'document.regex' => 'Informe um documento valido.',
            'password.required' => 'Informe uma senha.',
            'password.confirmed' => 'A confirmacao da senha nao confere.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
            'password.max' => 'A senha deve ter no maximo :max caracteres.',
            'password.regex' => 'Use pelo menos 1 letra e 1 numero na senha.',
        ], [
            'name' => 'nome',
            'email' => 'e-mail',
            'document' => 'documento',
            'phone_country' => 'codigo do pais',
            'phone_number' => 'telefone',
            'password' => 'senha',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Corrija os campos obrigatorios e tente novamente.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()
                ->withErrors($validator)
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('auth_modal', 'register');
        }

        $validated = $validator->validated();
    
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'document' => $validated['document'],
                'phone_country' => str_replace('+', '', $validated['phone_country']),
                'phone_number' => preg_replace('/\D/', '', $validated['phone_number']),
                'password' => Hash::make($validated['password']),
            ]);
        } catch (QueryException $e) {
            $isDuplicateEmail = (int) $e->getCode() === 23000
                && str_contains(strtolower($e->getMessage()), 'users_email_unique');

            if ($isDuplicateEmail) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Este e-mail ja esta cadastrado.',
                        'errors' => ['email' => ['Este e-mail ja esta cadastrado.']],
                    ], 422);
                }

                return back()
                    ->withErrors(['email' => 'Este e-mail ja esta cadastrado.'])
                    ->withInput($request->except(['password', 'password_confirmation']))
                    ->with('auth_modal', 'register');
            }

            Log::error('Falha ao criar usuario: '.$e->getMessage(), [
                'email' => $validated['email'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nao foi possivel concluir o cadastro agora. Tente novamente.',
                ], 500);
            }

            return back()
                ->withErrors(['email' => 'Nao foi possivel concluir o cadastro agora. Tente novamente.'])
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('auth_modal', 'register');
        }
    
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
    
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('user.profile.edit'),
                'message' => 'Conta criada com sucesso.',
            ]);
        }

        return redirect()
            ->route('user.profile.edit')
            ->with('success', 'Conta criada com sucesso. Complete seus dados de endereco para agilizar seus pedidos.');
    }    
}
