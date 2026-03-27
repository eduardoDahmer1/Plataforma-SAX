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

        return (new MailMessage)
            ->subject('Bem-vindo à Sax Department! Confirme seu e-mail')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('Estamos muito felizes em ter você conosco na Sax Department.')
            ->line('Para começar a aproveitar nossa plataforma e realizar suas compras, por favor, confirme seu endereço de e-mail clicando no botão abaixo.')
            ->action('Confirmar E-mail', $verificationUrl)
            ->line('Se você não criou uma conta, ignore este e-mail.')
            ->salutation('Atenciosamente, Equipe Sax.');
    }
}