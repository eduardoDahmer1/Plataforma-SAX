@props(['order', 'locale' => 'pt_BR'])

@php
    $translate = fn (string $key, array $replace = []) => app('translator')->get(
        "messages.{$key}",
        $replace,
        $locale
    );

    $copy = [
        'payment_status' => $translate('email_order_payment_status'),
        'pending' => $translate('email_order_pending'),
        'paid' => $translate('email_order_paid'),
        'failed' => $translate('email_order_failed'),
        'refunded' => $translate('email_order_refunded'),
        'delivery' => $translate('email_order_delivery'),
        'recipient' => $translate('email_order_recipient'),
        'pickup' => $translate('email_order_pickup'),
        'address' => $translate('email_order_address'),
        'notes' => $translate('email_order_notes'),
        'policies' => $translate('email_order_policies'),
        'accepted' => $translate('email_order_accepted'),
        'view_policies' => $translate('email_order_view_policies'),
    ];

    $paymentStatus = strtolower((string) ($order->payment_status ?: 'pending'));
    $paymentStatusLabel = $copy[$paymentStatus] ?? ucfirst($paymentStatus);
    $paymentStatusColor = match ($paymentStatus) {
        'paid' => '#1f7a37',
        'failed' => '#b42318',
        'refunded' => '#365899',
        default => '#9a6b00',
    };
    $storeKey = match ((int) $order->store) {
        1 => 'email_order_store_ciudad_del_este',
        2 => 'email_order_store_asuncion',
        3 => 'email_order_store_pedro_juan_caballero',
        default => 'email_order_store_default',
    };
    $storeName = $translate($storeKey);
    $isPickup = (int) $order->shipping === 3;
@endphp

<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="margin:0 0 1.8rem 0;border:1px solid #e6e2dc;">
    <tr>
        <td style="padding:1rem 1.3rem;background:#f8f6f2;">
            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.12rem;color:#777777;">
                {{ $copy['payment_status'] }}
            </span>
        </td>
        <td style="padding:1rem 1.3rem;background:#f8f6f2;text-align:right;">
            <strong style="font-size:0.82rem;text-transform:uppercase;color:{{ $paymentStatusColor }};">
                {{ $paymentStatusLabel }}
            </strong>
        </td>
    </tr>

    <tr>
        <td colspan="2" style="padding:1.2rem 1.3rem;border-top:1px solid #e6e2dc;">
            <div style="margin-bottom:0.8rem;font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12rem;color:#777777;">
                {{ $copy['delivery'] }}
            </div>
            <p style="margin:0 0 0.55rem 0;font-size:0.88rem;line-height:1.55;color:#222222;">
                <strong>{{ $copy['recipient'] }}:</strong>
                {{ trim($order->name . ' ' . $order->surname) }}
            </p>

            @if ($isPickup)
                <p style="margin:0;font-size:0.88rem;line-height:1.55;color:#222222;">
                    <strong>{{ $copy['pickup'] }}:</strong> {{ $storeName }}
                </p>
            @else
                <p style="margin:0;font-size:0.88rem;line-height:1.55;color:#222222;">
                    <strong>{{ $copy['address'] }}:</strong><br>
                    {{ $order->street }}, {{ $order->number }}
                    @if ($order->district) · {{ $order->district }} @endif
                    @if ($order->complement) · {{ $order->complement }} @endif
                    <br>
                    {{ $order->city }}@if ($order->state), {{ $order->state }}@endif
                    @if ($order->country) · {{ strtoupper((string) $order->country) }} @endif
                    @if ($order->cep) · {{ $order->cep }} @endif
                </p>
                @if ($order->observations)
                    <p style="margin:0.65rem 0 0 0;font-size:0.82rem;line-height:1.55;color:#666666;">
                        <strong>{{ $copy['notes'] }}:</strong> {{ $order->observations }}
                    </p>
                @endif
            @endif
        </td>
    </tr>

    @if ($order->terms_accepted_at)
        <tr>
            <td colspan="2" style="padding:1.2rem 1.3rem;border-top:1px solid #e6e2dc;background:#fbfaf8;">
                <div style="margin-bottom:0.5rem;font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12rem;color:#777777;">
                    {{ $copy['policies'] }}
                </div>
                <p style="margin:0;font-size:0.82rem;line-height:1.6;color:#444444;">
                    {{ str_replace(':date', $order->terms_accepted_at->format('d/m/Y H:i'), $copy['accepted']) }}
                    <a href="{{ route('policies.index') }}" style="color:#111111;font-weight:700;">
                        {{ $copy['view_policies'] }}
                    </a>
                </p>
            </td>
        </tr>
    @endif
</table>
