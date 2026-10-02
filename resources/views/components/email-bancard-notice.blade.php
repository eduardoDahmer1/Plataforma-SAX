@props(['order', 'copy'])

@if (strtolower((string) $order->payment_method) === 'bancard_v2')
    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="margin:0 0 20px;background:#f5f5f4;border:1px solid #d7d8da;">
        <tr>
            <td style="padding:16px 18px;color:#34373b;">
                <div style="margin:0 0 0.7rem 0;font-size:0.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;">
                    {{ $copy['bancard_title'] }}
                </div>

                @if ($order->payment_currency === 'PYG' && $order->payment_amount)
                    <table width="100%" cellpadding="0" cellspacing="0" border="0"
                           style="margin:0 0 0.8rem 0;border-top:1px solid #d7d8da;border-bottom:1px solid #d7d8da;">
                        <tr>
                            <td style="padding:0.65rem 0;font-size:0.78rem;color:#62666b;">
                                {{ $copy['bancard_amount'] }}
                            </td>
                            <td style="padding:0.65rem 0;text-align:right;font-size:1rem;font-weight:800;color:#25282c;">
                                G$ {{ number_format((float) $order->payment_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @if ($order->payment_exchange_rate)
                            <tr>
                                <td style="padding:0 0 0.65rem 0;font-size:0.75rem;color:#62666b;">
                                    {{ $copy['bancard_rate'] }}
                                </td>
                                <td style="padding:0 0 0.65rem 0;text-align:right;font-size:0.75rem;color:#62666b;">
                                    G$ {{ number_format((float) $order->payment_exchange_rate, 2, ',', '.') }} / US$ 1
                                </td>
                            </tr>
                        @endif
                    </table>
                @endif

                <p style="margin:0;font-size:0.82rem;line-height:1.65;color:#55595e;">
                    {{ $copy['bancard_bank_charges'] }}
                </p>
            </td>
        </tr>
    </table>
@endif
