<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    public function notifyAdmins(
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        array $data = []
    ): void {
        try {
            User::query()
                ->where('user_type', 1)
                ->eachById(function (User $admin) use ($type, $title, $message, $actionUrl, $data) {
                    $admin->adminNotifications()->create([
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'action_url' => $actionUrl,
                        'data' => $data,
                    ]);
                });
        } catch (\Throwable $exception) {
            Log::error('Não foi possível criar a notificação administrativa.', [
                'notification_type' => $type,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
