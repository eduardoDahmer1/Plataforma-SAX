<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private AdminNotificationService $notifications) {}

    public function created(User $user): void
    {
        $this->notifications->notifyAdmins(
            type: 'new_user',
            title: 'Novo usuário',
            message: "{$user->name} criou uma conta.",
            actionUrl: '/admin/clients',
            data: ['user_id' => $user->getKey()],
        );
    }
}
