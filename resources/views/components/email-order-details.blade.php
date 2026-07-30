@props(['order', 'locale' => 'pt_BR'])

@php
    $copy = match ($locale) {
        'en' => [
            'payment_status' => 'Payment status',
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Not approved',
            'refunded' => 'Refunded',
            'delivery' => 'Delivery information',
            'recipient' => 'Recipient',
            'pickup' => 'Pickup at',
            'address' => 'Delivery address',
            'notes' => 'Delivery notes',
            'policies' => 'Policies and terms',
            'accepted' => 'You accepted the Privacy, Purchase, Sales and Shipping Policies on :date.',
            'view_policies' => 'View policies and terms',
        ],
        'es' => [
            'payment_status' => 'Estado del pago',
            'pending' => 'Pendiente',
            'paid' => 'Pagado',
            'failed' => 'No aprobado',
            'refunded' => 'Reembolsado',
            'delivery' => 'Información de entrega',
            'recipient' => 'Destinatario',
            'pickup' => 'Retiro en',
            'address' => 'Dirección de entrega',
            'notes' => 'Observaciones de entrega',
            'policies' => 'Políticas y términos',
            'accepted' => 'Aceptaste las Políticas de Privacidad, Compras, Ventas y Envíos el :date.',
            'view_policies' => 'Ver políticas y términos',
        ],
        default => [
            'payment_status' => 'Status do pagamento',
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'failed' => 'Não aprovado',
            'refunded' => 'Reembolsado',
            'delivery' => 'Informações de entrega',
            'recipient' => 'Destinatário',
            'pickup' => 'Retirada em',
            'address' => 'Endereço de entrega',
            'notes' => 'Observações de entrega',
            'policies' => 'Políticas e termos',
            'accepted' => 'Você aceitou as Políticas de Privacidade, Compras, Vendas e Envios em :date.',
            'view_policies' => 'Ver políticas e termos',
        ],
    };

    $paymentStatus = strtolower((string) ($order->payment_status ?: 'pending'));
    $paymentStatusLabel = $copy[$paymentStatus] ?? ucfirst($paymentStatus);
    $paymentStatusColor = match ($paymentStatus) {
        'paid' => '#1f7a37',
        'failed' => '#b42318',
        'refunded' => '#365899',
        default => '#9a6b00',
    };
    $storeName = match ((int) $order->store) {
        1 => 'SAX Ciudad del Este',
        2 => 'SAX Assunção',
        3 => 'SAX Pedro Juan Caballero',
        default => 'SAX Department Store',
    };
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
