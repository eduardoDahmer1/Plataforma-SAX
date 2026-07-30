@props(['order', 'locale' => 'pt_BR'])

@php
    $copy = match ($locale) {
        'en' => [
            'view_order' => 'View my order',
            'pay_bancard' => 'Pay / retry with Bancard',
            'upload_receipt' => 'Upload deposit receipt',
            'view_receipt' => 'View my receipt',
            'whatsapp' => 'Talk to us on WhatsApp',
            'whatsapp_message' => 'Hello, I need help with order #:order.',
        ],
        'es' => [
            'view_order' => 'Ver mi pedido',
            'pay_bancard' => 'Pagar / reintentar con Bancard',
            'upload_receipt' => 'Enviar comprobante de depósito',
            'view_receipt' => 'Ver mi recibo',
            'whatsapp' => 'Hablar por WhatsApp',
            'whatsapp_message' => 'Hola, necesito ayuda con el pedido #:order.',
        ],
        default => [
            'view_order' => 'Ver meu pedido',
            'pay_bancard' => 'Pagar / tentar novamente com Bancard',
            'upload_receipt' => 'Enviar comprovante de depósito',
            'view_receipt' => 'Ver meu recibo',
            'whatsapp' => 'Falar pelo WhatsApp',
            'whatsapp_message' => 'Olá, preciso de ajuda com o pedido #:order.',
        ],
    };

    $orderReference = $order->order_number ?: $order->id;
    $orderUrl = route('user.orders.show', $order->id);
    $isPaid = $order->payment_status === 'paid' || $order->status === 'paid';
    $primaryUrl = $orderUrl;
    $primaryLabel = $copy['view_order'];

    if (!$isPaid && $order->payment_method === 'bancard_v2') {
        $primaryUrl = route('checkout.bancard.v2', $order->id);
        $primaryLabel = $copy['pay_bancard'];
    } elseif (!$isPaid && $order->payment_method === 'deposito') {
        $primaryLabel = $copy['upload_receipt'];
    } elseif ($isPaid && $order->receipt) {
        $primaryUrl = route('receipts.show', $order->receipt);
        $primaryLabel = $copy['view_receipt'];
    }

    $whatsappNumber = '595984167575';
    $whatsappMessage = str_replace(':order', $orderReference, $copy['whatsapp_message']);
    $whatsappUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . urlencode($whatsappMessage);
@endphp

<x-email-button :url="$primaryUrl">{{ $primaryLabel }}</x-email-button>

@if ($primaryUrl !== $orderUrl)
    <x-email-button :url="$orderUrl" background="#f0ece6" color="#111111">
        {{ $copy['view_order'] }}
    </x-email-button>
@endif

@if ($whatsappNumber !== '')
    <x-email-button :url="$whatsappUrl" background="#198754">
        {{ $copy['whatsapp'] }}
    </x-email-button>
@endif
