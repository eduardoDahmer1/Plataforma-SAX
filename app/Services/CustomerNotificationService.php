<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class CustomerNotificationService
{
    public function notifyUser(
        User|int|null $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        array $data = []
    ): void {
        try {
            $customer = $user instanceof User ? $user : User::find($user);

            if (! $customer || $customer->isAdmin()) {
                return;
            }

            $customer->adminNotifications()->create([
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'data' => $data,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Não foi possível criar a notificação do cliente.', [
                'notification_type' => $type,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function notifyCustomers(
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        array $data = []
    ): void {
        User::query()
            ->whereIn('user_type', [2, 3])
            ->eachById(fn (User $customer) => $this->notifyUser(
                $customer,
                $type,
                $title,
                $message,
                $actionUrl,
                $data
            ));
    }
}
