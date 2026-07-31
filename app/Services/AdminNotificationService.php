<?php

namespace App\Services;

use App\Mail\IntegrationAlertMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminNotificationService
{
    public function notifyAdmins(
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        array $data = [],
        bool $sendEmail = false,
    ): void {
        try {
            User::query()
                ->where('user_type', 1)
                ->eachById(function (User $admin) use ($type, $title, $message, $actionUrl, $data, $sendEmail) {
                    try {
                        $admin->adminNotifications()->create([
                            'type' => $type,
                            'title' => $title,
                            'message' => $message,
                            'action_url' => $actionUrl,
                            'data' => $data,
                        ]);
                    } catch (\Throwable $exception) {
                        Log::error('Não foi possível criar a notificação administrativa.', [
                            'notification_type' => $type,
                            'message' => $exception->getMessage(),
                        ]);
                    }

                    if (! $sendEmail || ! filter_var($admin->email, FILTER_VALIDATE_EMAIL)) {
                        return;
                    }

                    try {
                        Mail::to($admin->email)->send(new IntegrationAlertMail(
                            type: $type,
                            title: $title,
                            alertMessage: $message,
                            actionUrl: $actionUrl,
                            details: $data,
                        ));
                    } catch (\Throwable $exception) {
                        Log::error('Não foi possível enviar o e-mail de alerta da integração.', [
                            'notification_type' => $type,
                            'recipient' => $admin->email,
                            'message' => $exception->getMessage(),
                        ]);
                    }
                });
        } catch (\Throwable $exception) {
            Log::error('Não foi possível criar a notificação administrativa.', [
                'notification_type' => $type,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
