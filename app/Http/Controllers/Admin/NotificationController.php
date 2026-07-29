<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, Notification $notification): RedirectResponse|JsonResponse
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'notification_id' => $notification->id,
                'destination' => $destination,
            ]);
        }

        return redirect()->to($destination);
    }

    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $admin = auth()->user();

        if (! $admin || ! $admin->isAdmin()) {
            abort(403);
        }

        $updated = $admin->adminNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'updated' => $updated,
            ]);
        }

        return redirect()->back();
    }
}
