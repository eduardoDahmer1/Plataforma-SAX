@extends('layout.email')

@section('title', 'Pagamento Confirmado')

@section('content')

    <p style="margin:0 0 0.5rem 0;font-size:0.75rem;letter-spacing:0.2rem;text-transform:uppercase;color:#888888;">Olá,</p>
    <h1 style="margin:0 0 1.5rem 0;font-size:1.75rem;font-weight:900;text-transform:uppercase;letter-spacing:0.1rem;color:#000000;">{{ $order->name }}</h1>

    <p style="margin:0 0 1.5rem 0;font-size:0.9375rem;color:#333333;line-height:1.6;">
        Seu pagamento foi <strong>confirmado com sucesso</strong>.<br>
        Já estamos preparando o seu pedido com carinho!
    </p>

    {{-- Caja resumen --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0ece6;margin-bottom:1.5rem;">
        <tr>
            <td style="padding:1.25rem 1.5rem;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="padding:0.5rem 0;border-bottom:1px solid #ddd9d4;">
                            <span style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">Número</span>
                        </td>
                        <td style="padding:0.5rem 0;border-bottom:1px solid #ddd9d4;text-align:right;">
                            <span style="font-size:0.875rem;font-weight:700;color:#000000;">#{{ $order->order_number ?? $order->id }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0.5rem 0;border-bottom:1px solid #ddd9d4;">
                            <span style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">Data</span>
                        </td>
                        <td style="padding:0.5rem 0;border-bottom:1px solid #ddd9d4;text-align:right;">
                            <span style="font-size:0.875rem;color:#333333;">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0.5rem 0;border-bottom:1px solid #ddd9d4;">
                            <span style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">Pagamento</span>
                        </td>
                        <td style="padding:0.5rem 0;border-bottom:1px solid #ddd9d4;text-align:right;">
                            <span style="font-size:0.875rem;color:#333333;">{{ ucfirst($order->payment_method) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0.75rem 0 0 0;">
                            <span style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.15rem;color:#888888;">Total</span>
                        </td>
                        <td style="padding:0.75rem 0 0 0;text-align:right;">
                            @foreach (order_all_currencies($order->total) as $valor)
                                <div style="font-size:1.125rem;font-weight:900;color:#000000;line-height:1.5;">{{ $valor }}</div>
                            @endforeach
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 1.5rem 0;font-size:0.9375rem;color:#333333;">
        Acompanhe os detalhes do seu pedido clicando no botão abaixo:
    </p>

    <x-email-button :url="route('user.orders.show', $order->id)">Ver meu pedido</x-email-button>

@endsection
