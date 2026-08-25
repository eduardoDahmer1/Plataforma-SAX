<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\AdminNotificationService;
use App\Services\RendixPixService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RendixRefundRequestController extends Controller
{
    public function store(
        Request $request,
        Order $order,
        AdminNotificationService $notifications,
    ): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()?->getKey(), 403);

        $validated = $request->validate([
            'reason' => ['required', Rule::in(['changed_mind', 'duplicate', 'wrong_data', 'delivery', 'other'])],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = DB::transaction(function () use ($order, $validated): Order {
            $lockedOrder = Order::query()
                ->with('paymentTransactions')
                ->lockForUpdate()
                ->findOrFail($order->getKey());

            if ($lockedOrder->refund_request_status === 'pending') {
                throw ValidationException::withMessages([
                    'reason' => __('messages.rendix_refund_request_already_pending'),
                ]);
            }

            $hasRefundableTransaction = $lockedOrder->paymentTransactions
                ->where('provider', RendixPixService::PROVIDER)
                ->where('status', 'paid')
                ->whereNull('refunded_at')
                ->whereNotNull('external_id')
                ->isNotEmpty();

            $unavailableOrderStatus = in_array($lockedOrder->status, [
                'shipped', 'completed', 'delivered', 'canceled', 'cancelled', 'failed',
            ], true);

            if (
                $lockedOrder->payment_method !== RendixPixService::PROVIDER
                || $lockedOrder->payment_status !== 'paid'
                || $unavailableOrderStatus
                || ! $hasRefundableTransaction
            ) {
                throw ValidationException::withMessages([
                    'reason' => __('messages.rendix_refund_request_not_available'),
                ]);
            }

            $lockedOrder->update([
                'refund_request_status' => 'pending',
                'refund_request_reason' => $validated['reason'],
                'refund_request_details' => trim((string) ($validated['details'] ?? '')) ?: null,
                'refund_requested_at' => now(),
                'refund_request_resolved_at' => null,
                'refund_request_resolved_by' => null,
            ]);

            return $lockedOrder;
        });

        $reference = $order->order_number ?: $order->getKey();
        $notifications->notifyAdmins(
            type: 'rendix_refund_request',
            title: __('messages.rendix_refund_request_admin_title'),
            message: __('messages.rendix_refund_request_admin_message', ['reference' => $reference]),
            actionUrl: "/admin/orders/{$order->getKey()}",
            data: [
                'order_id' => $order->getKey(),
                'reason' => $order->refund_request_reason,
                'translation_params' => ['reference' => $reference],
            ],
        );

        return redirect()
            ->route('user.orders.show', $order)
            ->with('success', __('messages.rendix_refund_request_success'));
    }
}
