<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\AdminNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private AdminNotificationService $notifications) {}

    public function created(Order $order): void
    {
        $reference = $order->order_number ?: $order->getKey();

        $this->notifications->notifyAdmins(
            type: 'new_order',
            title: 'Novo pedido',
            message: "O pedido #{$reference} foi recebido.",
            actionUrl: "/admin/orders/{$order->getKey()}",
            data: ['order_id' => $order->getKey()],
        );
    }
}
