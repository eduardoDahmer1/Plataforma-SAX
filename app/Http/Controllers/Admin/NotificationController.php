<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function markAsRead(Notification $notification): RedirectResponse
    {
        if ((int) $notification->user_id !== (int) auth()->id()) {
            abort(404);
        }

        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        $destination = $notification->action_url;

        if (
            ! is_string($destination)
            || ! str_starts_with($destination, '/')
            || str_starts_with($destination, '//')
        ) {
            $destination = route('admin.index');
        }

        return redirect()->to($destination);
    }

    public function markAllAsRead(): RedirectResponse
    {
        $admin = auth()->user();

        if (! $admin || ! $admin->isAdmin()) {
            abort(403);
        }

        $admin->adminNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back();
    }
}
