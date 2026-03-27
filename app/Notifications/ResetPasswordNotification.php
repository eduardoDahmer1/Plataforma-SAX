<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;

class ResetPasswordNotification extends ResetPasswordBase
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Recuperação de Senha - Sax Department')
            ->greeting('Olá!')
            ->line('Você está recebendo este e-mail porque solicitou a redefinição de senha da sua conta.')
            ->action('Redefinir Senha', url(config('app.url').route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false)))
            ->line('Este link de redefinição expirará em ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' minutos.')
            ->line('Se você não solicitou isso, ignore este e-mail.')
            ->salutation('Atenciosamente, Equipe Sax.');
    }
}