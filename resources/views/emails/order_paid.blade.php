@extends('layout.email')

@section('title', app('translator')->get('messages.email_order_paid_title', [], $emailLocale ?? 'pt_BR'))

@section('content')

    @php
        $locale = $emailLocale ?? 'pt_BR';

        $translate = fn (string $key, array $replace = []) => app('translator')->get(
            "messages.{$key}",
            $replace,
            $locale
        );

        $copy = [
            'hello' => $translate('email_order_hello'),
            'intro' => $translate('email_order_paid_intro'),
            'intro2' => $translate('email_order_paid_preparing'),
            'number' => $translate('email_order_number'),
            'date' => $translate('email_order_date'),
            'payment' => $translate('email_order_payment'),
            'total' => $translate('email_order_total'),
            'track' => $translate('email_order_paid_track'),
            'paid_title' => $translate('email_order_paid_status'),
            'method_deposito' => $translate('email_order_bank_deposit'),
            'items' => $translate('email_order_items'),
            'qty' => $translate('email_order_quantity'),
            'subtotal' => $translate('email_order_subtotal'),
            'discount' => $translate('email_order_discount'),
            'shipping' => $translate('email_order_shipping'),
            'shipping_free' => $translate('email_order_free'),
            'bancard_title' => $translate('email_order_bancard_title'),
            'bancard_amount' => $translate('email_order_bancard_amount'),
            'bancard_rate' => $translate('email_order_bancard_rate'),
            'bancard_bank_charges' => $translate('email_order_bancard_bank_charges'),
        ];

        // Valores na moeda em que o cliente fechou o pedido.
        $dinheiro = fn ($valorBase) => order_money($order, $valorBase);

        $itens = $order->items;
        $subtotal = $itens->sum(fn ($item) => $item->price * $item->quantity);
        $desconto = (float) ($order->discount ?? 0);
        $frete = (float) ($order->shipping_cost ?? 0);
        $totalFormatted = $dinheiro($order->total);
        $paymentMethod = strtolower((string) $order->payment_method) === 'deposito'
            ? $copy['method_deposito']
            : ucfirst((string) $order->payment_method);
    @endphp

    <p style="margin:0 0 0.6rem 0;font-size:0.76rem;letter-spacing:0.2rem;text-transform:uppercase;color:#8a8a8a;">{{ $copy['hello'] }}</p>
    <h1 style="margin:0 0 1.8rem 0;font-size:2rem;font-weight:900;text-transform:uppercase;letter-spacing:0.07rem;color:#111111;line-height:1.15;">{{ $order->name }}</h1>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 1.5rem 0;">
        <tr>
            <td style="background:#f4f1ec;border-left:4px solid #000;padding:0.9rem 1rem;">
                <span style="font-size:0.82rem;font-weight:700;color:#222222;text-transform:uppercase;letter-spacing:0.08rem;">{{ $copy['paid_title'] }}</span>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 1.6rem 0;font-size:1rem;color:#2f2f2f;line-height:1.75;">
        <strong>{{ $copy['intro'] }}</strong><br>
        {{ $copy['intro2'] }}
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0ece6;margin-bottom:1.8rem;border:1px solid #e3ddd6;">
        <tr>
            <td style="padding:1.4rem 1.6rem;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">{{ $copy['number'] }}</span>
                        </td>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;text-align:right;">
                            <span style="font-size:0.95rem;font-weight:800;color:#000000;">#{{ $order->order_number ?? $order->id }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">{{ $copy['date'] }}</span>
                        </td>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;text-align:right;">
                            <span style="font-size:0.95rem;color:#333333;">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">{{ $copy['payment'] }}</span>
                        </td>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;text-align:right;">
                            <span style="font-size:0.95rem;color:#333333;">{{ $paymentMethod }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">{{ $copy['subtotal'] }}</span>
                        </td>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;text-align:right;">
                            <span style="font-size:0.95rem;color:#333333;">{{ $dinheiro($subtotal) }}</span>
                        </td>
                    </tr>

                    @if ($desconto > 0)
                        <tr>
                            <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;">
                                <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">{{ $copy['discount'] }}</span>
                                @if ($order->cupon)
                                    <span style="display:inline-block;margin-left:6px;font-size:0.65rem;font-weight:700;letter-spacing:0.08rem;color:#111;border:1px dashed #bdbdbd;background:#fff;padding:2px 6px;">{{ $order->cupon->codigo }}</span>
                                @endif
                            </td>
                            <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;text-align:right;">
                                <span style="font-size:0.95rem;font-weight:700;color:#1f7a37;">- {{ $dinheiro($desconto) }}</span>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">{{ $copy['shipping'] }}</span>
                        </td>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;text-align:right;">
                            <span style="font-size:0.95rem;color:#333333;">{{ $frete > 0 ? $dinheiro($frete) : $copy['shipping_free'] }}</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0.85rem 0 0 0;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">{{ $copy['total'] }}</span>
                        </td>
                        <td style="padding:0.85rem 0 0 0;text-align:right;">
                            <div style="font-size:1.35rem;font-weight:900;color:#000000;line-height:1.2;">{{ $totalFormatted }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <x-email-bancard-notice :order="$order" :copy="$copy" />

    <x-email-order-details :order="$order" :locale="$locale" />

    {{-- Produtos do pedido --}}
    @if ($itens->count())
        <p style="margin:0 0 0.8rem 0;font-size:0.72rem;letter-spacing:0.18rem;text-transform:uppercase;color:#8a8a8a;">{{ $copy['items'] }}</p>

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:1.8rem;border:1px solid #e6e2dc;">
            @foreach ($itens as $item)
                <tr>
                    <td style="padding:0.9rem 1rem;border-bottom:{{ $loop->last ? '0' : '1px solid #eeeae4' }};">
                        <span style="display:block;font-size:0.92rem;font-weight:700;color:#111111;line-height:1.4;">
                            {{ $item->external_name ?: ($item->name ?: $translate('email_order_product')) }}
                        </span>
                        <span style="display:block;margin-top:2px;font-size:0.72rem;color:#8a8a8a;">
                            SKU: {{ $item->sku ?: '-' }} &nbsp;·&nbsp; {{ $copy['qty'] }}: {{ $item->quantity }}
                        </span>
                    </td>
                    <td style="padding:0.9rem 1rem;border-bottom:{{ $loop->last ? '0' : '1px solid #eeeae4' }};text-align:right;white-space:nowrap;">
                        <span style="font-size:0.95rem;font-weight:700;color:#111111;">{{ $dinheiro($item->price * $item->quantity) }}</span>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    <p style="margin:0 0 1.8rem 0;font-size:0.98rem;color:#333333;line-height:1.7;">
        {{ $copy['track'] }}
    </p>

    <x-email-order-actions :order="$order" :locale="$locale" />

@endsection
