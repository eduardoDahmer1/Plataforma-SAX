<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Exibe a tela para solicitar o link de recuperação de senha.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Processa o envio do link de recuperação por e-mail.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // O Laravel usa o Broker de Password para gerar o token e enviar o e-mail
        // usando a configuração que validamos no .env (Umbler).
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($request->expectsJson()) {
            return $status == Password::RESET_LINK_SENT
                ? response()->json(['success' => true,  'message' => __($status)])
                : response()->json(['success' => false, 'message' => __($status)], 422);
        }

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
    }
}