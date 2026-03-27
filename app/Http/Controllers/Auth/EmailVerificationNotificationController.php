<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Envia uma nova notificação de verificação de e-mail.
     */
    public function store(Request $request): RedirectResponse
    {
        // Se o usuário já verificou enquanto a página estava aberta, manda direto para Home
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // Este método dispara o e-mail usando o seu SMTP da Umbler configurado no .env
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}