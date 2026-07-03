<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class WelcomeAndVerifyEmail extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Gera a URL assinada de verificação
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );

        $locale = $this->resolveLocaleFromUser($notifiable);
        $subject = match ($locale) {
            'en' => 'Welcome to SAX Department! Verify your email',
            'es' => 'Bienvenido a SAX Department! Verifica tu correo',
            default => 'Bem-vindo à SAX Department! Confirme seu e-mail',
        };

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.welcome', [
                'user'            => $notifiable,
                'verificationUrl' => $verificationUrl,
                'logoUrl'         => \App\Models\Attribute::logoUrl(),
                'emailLocale'     => $locale,
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