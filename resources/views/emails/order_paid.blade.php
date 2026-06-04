<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Confirmado</title>
</head>
<body style="margin:0;padding:0;background-color:#f0ece6;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0ece6;">
        <tr>
            <td align="center" style="padding:2rem 1rem;">

                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

                    {{-- Header negro --}}
                    <tr>
                        <td style="background-color:#000000;padding:2rem;text-align:center;">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="SAX" style="max-height:3rem;width:auto;display:block;margin:0 auto;filter:invert(1);">
                            @else
                                <p style="margin:0;color:#ffffff;font-size:2rem;font-weight:900;letter-spacing:0.5rem;">SAX</p>
                                <p style="margin:0.25rem 0 0 0;color:#cccccc;font-size:0.625rem;letter-spacing:0.3rem;">STYLE &bull; ARTS &bull; XTRAS</p>
                            @endif
                        </td>
                    </tr>

                    {{-- Cuerpo blanco --}}
                    <tr>
                        <td style="background-color:#ffffff;padding:2.5rem 2rem;">

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
                                                    <span style="font-size:1.125rem;font-weight:900;color:#000000;">{{ $order->currency_sign }} {{ number_format($order->total, 2, ',', '.') }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 1.5rem 0;font-size:0.9375rem;color:#333333;">
                                Acompanhe os detalhes do seu pedido clicando no botão abaixo:
                            </p>

                            {{-- Botón --}}
                            <table cellpadding="0" cellspacing="0" border="0" align="center" style="margin:1.5rem auto 0 auto;">
                                <tr>
                                    <td style="background-color:#000000;">
                                        <a href="{{ route('user.orders.show', $order->id) }}"
                                           style="display:inline-block;padding:0.875rem 2rem;color:#ffffff;text-decoration:none;font-size:0.75rem;font-weight:700;letter-spacing:0.2rem;text-transform:uppercase;">
                                            Ver meu pedido
                                        </a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#eeebe7;padding:1.5rem;text-align:center;">
                            <p style="margin:0;font-size:0.75rem;color:#888888;letter-spacing:0.1rem;">SAX Department Store</p>
                            <p style="margin:0.25rem 0 0 0;font-size:0.6875rem;color:#aaaaaa;">Ciudad del Este, Paraguai &bull; Foz do Iguaçu, Brasil</p>
                            <p style="margin:0.5rem 0 0 0;font-size:0.6875rem;color:#bbbbbb;">&copy; {{ date('Y') }} SAX. Todos os direitos reservados.</p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
