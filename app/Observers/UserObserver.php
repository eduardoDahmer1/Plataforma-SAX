<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AdminNotificationService;
use App\Services\CustomerNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private AdminNotificationService $notifications,
        private CustomerNotificationService $customerNotifications
    ) {}

    public function created(User $user): void
    {
        $this->notifications->notifyAdmins(
            type: 'new_user',
            title: 'Novo usuário',
            message: "{$user->name} criou uma conta.",
            actionUrl: "/admin/clients/{$user->getKey()}",
            data: ['user_id' => $user->getKey()],
        );

        $this->customerNotifications->notifyUser(
            $user,
            'customer_welcome',
            'Bem-vindo à SAX',
            'Sua conta foi criada. Complete seus dados para aproveitar uma experiência personalizada.',
            '/dashboard',
        );
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('password')) {
            $this->customerNotifications->notifyUser($user, 'customer_password_changed', 'Senha alterada', 'A senha da sua conta foi alterada com sucesso.', '/seguranca/senha');
        }

        if ($user->wasChanged('email')) {
            $this->customerNotifications->notifyUser($user, 'customer_email_changed', 'E-mail alterado', "O e-mail da sua conta foi alterado para {$user->email}.", '/dashboard');
        }

        if ($user->wasChanged('email_verified_at') && $user->email_verified_at) {
            $this->customerNotifications->notifyUser($user, 'customer_email_verified', 'E-mail verificado', 'Seu endereço de e-mail foi confirmado com sucesso.', '/dashboard');
        }

        $profileFields = ['name', 'phone_country', 'phone_number', 'address', 'number', 'district', 'city', 'state', 'country', 'cep'];
        if ($user->wasChanged($profileFields)) {
            $this->customerNotifications->notifyUser($user, 'customer_profile_updated', 'Cadastro atualizado', 'Os dados do seu perfil foram atualizados.', '/dashboard');
        }
    }
}
