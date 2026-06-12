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
            ->subject('Bem-vindo à SAX Department! Confirme seu e-mail')
            ->view('emails.welcome', [
                'user'            => $notifiable,
                'verificationUrl' => $verificationUrl,
                'logoUrl'         => \App\Models\Attribute::logoUrl(),
            ]);
    }
}