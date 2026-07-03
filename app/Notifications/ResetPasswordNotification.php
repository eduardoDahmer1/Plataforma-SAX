<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;

class ResetPasswordNotification extends ResetPasswordBase
{
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(config('app.url') . route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

        $locale = $this->resolveLocaleFromUser($notifiable);
        $subject = match ($locale) {
            'en' => 'Password Reset - SAX Department',
            'es' => 'Recuperacion de Contrasena - SAX Department',
            default => 'Recuperação de Senha - SAX Department',
        };

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.reset_password', [
                'resetUrl'      => $resetUrl,
                'expireMinutes' => $expireMinutes,
                'logoUrl'       => \App\Models\Attribute::logoUrl(),
                'emailLocale'   => $locale,
            ]);
    }

    private function resolveLocaleFromUser($user): string
    {
        $country = strtolower((string) ($user->country ?? ''));

        if (in_array($country, ['brasil', 'br', 'brazil'], true)) {
            return 'pt_BR';
        }

        if (in_array($country, ['paraguai', 'paraguay', 'py'], true)) {
            return 'es';
        }

        return 'en';
    }
}