<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    private const CATEGORY_TYPES = [
        'orders' => ['new_order', 'order_processing', 'order_shipped', 'order_completed', 'order_canceled'],
        'payments' => ['deposit_receipt', 'payment_paid', 'payment_failed', 'payment_refunded', 'rendix_refund_request'],
        'checkout' => ['checkout_error', 'high_value_abandoned_cart', 'abandoned_cart_feedback'],
        'clients' => ['new_user'],
        'contacts' => ['new_contact', 'new_resume'],
        'catalog' => ['low_stock', 'out_of_stock'],
        'system' => ['integration_failed', 'integration_stale', 'integration_recovered'],
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', Rule::in(array_keys(self::CATEGORY_TYPES))],
            'status' => ['nullable', Rule::in(['active', 'archived', 'all'])],
            'per_page' => ['nullable', Rule::in([10, 20, 30, 50, 100])],
        ]);

        $admin = auth()->user();
        $search = trim((string) ($validated['search'] ?? ''));
        $category = $validated['category'] ?? null;
        $status = $validated['status'] ?? 'active';
        $perPage = (int) ($validated['per_page'] ?? 30);

        $query = $admin->adminNotifications()->latest();

        if ($status === 'active') {
            $query->whereNull('archived_at');
        } elseif ($status === 'archived') {
            $query->whereNotNull('archived_at');
        }

        if ($category) {
            $query->whereIn('type', self::CATEGORY_TYPES[$category]);
        }

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $baseQuery = $admin->adminNotifications();

        return view('admin.notifications.index', [
            'notifications' => $query->paginate($perPage)->withQueryString(),
            'search' => $search,
            'category' => $category,
            'status' => $status,
            'perPage' => $perPage,
            'categoryTypes' => self::CATEGORY_TYPES,
            'stats' => [
                'total' => (clone $baseQuery)->count(),
                'active' => (clone $baseQuery)->whereNull('archived_at')->count(),
                'archived' => (clone $baseQuery)->whereNotNull('archived_at')->count(),
                'unread' => (clone $baseQuery)->whereNull('archived_at')->whereNull('read_at')->count(),
            ],
        ]);
    }

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
            ->whereNull('archived_at')
            ->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'updated' => $updated,
            ]);
        }

        return redirect()->back();
    }

    public function archive(Request $request, Notification $notification): RedirectResponse|JsonResponse
    {
        $this->ensureOwnedByCurrentAdmin($notification);

        $notification->update([
            'archived_at' => now(),
            'read_at' => $notification->read_at ?? now(),
        ]);

        return $this->actionResponse($request, 1);
    }

    public function restore(Request $request, Notification $notification): RedirectResponse|JsonResponse
    {
        $this->ensureOwnedByCurrentAdmin($notification);
        $notification->update(['archived_at' => null]);

        return $this->actionResponse($request, 1);
    }

    public function destroy(Request $request, Notification $notification): RedirectResponse|JsonResponse
    {
        $this->ensureOwnedByCurrentAdmin($notification);
        $notification->delete();

        return $this->actionResponse($request, 1);
    }

    public function bulkAction(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['archive', 'restore', 'delete'])],
            'notification_ids' => ['required', 'array', 'min:1'],
            'notification_ids.*' => ['integer'],
        ]);

        $notifications = auth()->user()->adminNotifications()
            ->whereIn('id', array_unique($validated['notification_ids']));

        $updated = match ($validated['action']) {
            'archive' => $notifications->update([
                'archived_at' => now(),
                'read_at' => now(),
            ]),
            'restore' => $notifications->update(['archived_at' => null]),
            'delete' => $notifications->delete(),
        };

        return $this->actionResponse($request, $updated);
    }

    private function ensureOwnedByCurrentAdmin(Notification $notification): void
    {
        if ((int) $notification->user_id !== (int) auth()->id()) {
            abort(404);
        }
    }

    private function actionResponse(Request $request, int $updated): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'updated' => $updated,
            ]);
        }

        return redirect()->back();
    }
}
