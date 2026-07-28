<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\AdminNotificationService;
use App\Services\CustomerNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private AdminNotificationService $notifications,
        private CustomerNotificationService $customerNotifications
    ) {}

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

        $this->customerNotifications->notifyUser(
            $order->user_id,
            'customer_order_created',
            'Pedido recebido',
            "Recebemos seu pedido #{$reference}. Você pode acompanhar cada etapa por aqui.",
            "/orders/{$order->getKey()}",
            ['order_id' => $order->getKey()],
        );
    }

    public function updated(Order $order): void
    {
        $reference = $order->order_number ?: $order->getKey();
        $url = "/admin/orders/{$order->getKey()}";
        $data = ['order_id' => $order->getKey()];

        if ($order->wasChanged('deposit_receipt') && filled($order->deposit_receipt)) {
            $this->notifications->notifyAdmins('deposit_receipt', 'Comprovante recebido', "O pedido #{$reference} recebeu um comprovante.", $url, $data);
        }

        if ($order->wasChanged('payment_status')) {
            $paymentNotifications = [
                'paid' => ['payment_paid', 'Pagamento aprovado', "O pagamento do pedido #{$reference} foi aprovado."],
                'failed' => ['payment_failed', 'Pagamento recusado', "O pagamento do pedido #{$reference} falhou ou foi recusado."],
                'refunded' => ['payment_refunded', 'Pagamento reembolsado', "O pagamento do pedido #{$reference} foi reembolsado."],
            ];

            if ($notification = $paymentNotifications[$order->payment_status] ?? null) {
                $this->notifications->notifyAdmins($notification[0], $notification[1], $notification[2], $url, $data);
            }

            $customerPayments = [
                'paid' => ['customer_payment_paid', 'Pagamento aprovado', "O pagamento do pedido #{$reference} foi aprovado."],
                'failed' => ['customer_payment_failed', 'Pagamento não aprovado', "Não foi possível aprovar o pagamento do pedido #{$reference}. Verifique o pedido para tentar novamente."],
                'refunded' => ['customer_payment_refunded', 'Pagamento reembolsado', "O pagamento do pedido #{$reference} foi reembolsado."],
            ];

            if ($notification = $customerPayments[$order->payment_status] ?? null) {
                $this->customerNotifications->notifyUser($order->user_id, $notification[0], $notification[1], $notification[2], "/orders/{$order->getKey()}", $data);
            }
        }

        if ($order->wasChanged('status')) {
            $statusNotifications = [
                'processing' => ['order_processing', 'Pedido em processamento', "O pedido #{$reference} entrou em processamento."],
                'shipped' => ['order_shipped', 'Pedido enviado', "O pedido #{$reference} foi marcado como enviado."],
                'completed' => ['order_completed', 'Pedido concluído', "O pedido #{$reference} foi concluído."],
                'canceled' => ['order_canceled', 'Pedido cancelado', "O pedido #{$reference} foi cancelado."],
            ];

            if ($notification = $statusNotifications[$order->status] ?? null) {
                $this->notifications->notifyAdmins($notification[0], $notification[1], $notification[2], $url, $data);
            }

            $customerStatuses = [
                'processing' => ['customer_order_processing', 'Pedido em preparação', "Seu pedido #{$reference} está sendo preparado."],
                'shipped' => ['customer_order_shipped', 'Pedido enviado', "Seu pedido #{$reference} foi enviado."],
                'completed' => ['customer_order_completed', 'Pedido concluído', "Seu pedido #{$reference} foi concluído."],
                'canceled' => ['customer_order_canceled', 'Pedido cancelado', "Seu pedido #{$reference} foi cancelado."],
            ];

            if ($notification = $customerStatuses[$order->status] ?? null) {
                $this->customerNotifications->notifyUser($order->user_id, $notification[0], $notification[1], $notification[2], "/orders/{$order->getKey()}", $data);
            }
        }
    }
}
