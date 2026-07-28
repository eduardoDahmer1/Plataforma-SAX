<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;

class UserNotificationController extends Controller
{
    public function markAsRead(Notification $notification): RedirectResponse
    {
        abort_if(auth()->user()->isAdmin(), 403);

        if ((int) $notification->user_id !== (int) auth()->id()) {
            abort(404);
        }

        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        $destination = $notification->action_url;
        if (! is_string($destination) || ! str_starts_with($destination, '/') || str_starts_with($destination, '//') || str_starts_with($destination, '/admin')) {
            $destination = route('user.dashboard');
        }

        return redirect()->to($destination);
    }

    public function markAllAsRead(): RedirectResponse
    {
        abort_if(auth()->user()->isAdmin(), 403);

        auth()->user()->adminNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->back();
    }
}
