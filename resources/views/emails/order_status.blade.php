@extends('layout.email')

@section('title', 'Atualização do seu Pedido')

@section('content')

    @php
        $locale = $emailLocale ?? 'pt_BR';

        $copy = match ($locale) {
            'en' => [
                'hello' => 'Hello,',
                'order' => 'Order',
                'date' => 'Date',
                'payment' => 'Payment',
                'total' => 'Total',
                'track' => 'You can track your order details by clicking the button below:',
                'cta' => 'View my order',
                'updated_title' => 'Your order has been updated',
                'method_deposito' => 'Bank deposit',
            ],
            'es' => [
                'hello' => 'Hola,',
                'order' => 'Pedido',
                'date' => 'Fecha',
                'payment' => 'Pago',
                'total' => 'Total',
                'track' => 'Puedes seguir el estado detallado haciendo clic en el boton abajo:',
                'cta' => 'Ver mi pedido',
                'updated_title' => 'Tu pedido fue actualizado',
                'method_deposito' => 'Deposito bancario',
            ],
            default => [
                'hello' => 'Olá,',
                'order' => 'Pedido',
                'date' => 'Data',
                'payment' => 'Pagamento',
                'total' => 'Total',
                'track' => 'Você pode acompanhar o status detalhado clicando no botão abaixo:',
                'cta' => 'Ver meu pedido',
                'updated_title' => 'Seu pedido foi atualizado',
                'method_deposito' => 'Deposito bancario',
            ],
        };

        $currencySign = $order->currency_sign ?: 'US$';
        $currencyValue = (float) ($order->currency_value ?: 1);
        $totalConverted = (float) $order->total * $currencyValue;
        $totalFormatted = $currencySign . ' ' . number_format($totalConverted, 2, ',', '.');
        $paymentMethod = strtolower((string) $order->payment_method) === 'deposito'
            ? $copy['method_deposito']
            : ucfirst((string) $order->payment_method);
    @endphp

    <p style="margin:0 0 0.6rem 0;font-size:0.76rem;letter-spacing:0.2rem;text-transform:uppercase;color:#8a8a8a;">{{ $copy['hello'] }}</p>
    <h1 style="margin:0 0 1.8rem 0;font-size:2rem;font-weight:900;text-transform:uppercase;letter-spacing:0.07rem;color:#111111;line-height:1.15;">{{ $order->name }}</h1>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 1.5rem 0;">
        <tr>
            <td style="background:#f4f1ec;border-left:4px solid #000;padding:0.9rem 1rem;">
                <span style="font-size:0.82rem;font-weight:700;color:#222222;text-transform:uppercase;letter-spacing:0.08rem;">{{ $copy['updated_title'] }}</span>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 1.6rem 0;font-size:1rem;color:#2f2f2f;line-height:1.75;">
        {{ $messageCustom }}
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0ece6;margin-bottom:1.8rem;border:1px solid #e3ddd6;">
        <tr>
            <td style="padding:1.4rem 1.6rem;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="padding:0.58rem 0;border-bottom:1px solid #ddd9d4;">
                            <span style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">{{ $copy['order'] }}</span>
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

    <p style="margin:0 0 1.8rem 0;font-size:0.98rem;color:#333333;line-height:1.7;">
        {{ $copy['track'] }}
    </p>

    <x-email-button :url="route('user.orders.show', $order->id)">{{ $copy['cta'] }}</x-email-button>

@endsection
