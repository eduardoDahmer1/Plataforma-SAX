@props(['order', 'locale' => 'pt_BR'])

@php
    $translate = fn (string $key, array $replace = []) => app('translator')->get(
        "messages.{$key}",
        $replace,
        $locale
    );

    $copy = [
        'view_order' => $translate('email_order_view_order'),
        'pay_bancard' => $translate('email_order_pay_bancard'),
        'upload_receipt' => $translate('email_order_upload_receipt'),
        'view_receipt' => $translate('email_order_view_receipt'),
        'whatsapp' => $translate('email_order_whatsapp'),
        'whatsapp_message' => $translate('email_order_whatsapp_message'),
    ];

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
