@props(['order', 'copy'])

@if (strtolower((string) $order->payment_method) === 'bancard_v2')
    <table width="100%" cellpadding="0" cellspacing="0" border="0"
           style="margin:0 0 1.8rem 0;background:#fff9e9;border:1px solid #e9d294;">
        <tr>
            <td style="padding:1.2rem 1.4rem;color:#3f3520;">
                <div style="margin:0 0 0.7rem 0;font-size:0.78rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08rem;">
                    {{ $copy['bancard_title'] }}
                </div>

                @if ($order->payment_currency === 'PYG' && $order->payment_amount)
                    <table width="100%" cellpadding="0" cellspacing="0" border="0"
                           style="margin:0 0 0.8rem 0;border-top:1px solid #eadcae;border-bottom:1px solid #eadcae;">
                        <tr>
                            <td style="padding:0.65rem 0;font-size:0.78rem;color:#6c5b32;">
                                {{ $copy['bancard_amount'] }}
                            </td>
                            <td style="padding:0.65rem 0;text-align:right;font-size:1rem;font-weight:800;color:#2f2818;">
                                G$ {{ number_format((float) $order->payment_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @if ($order->payment_exchange_rate)
                            <tr>
                                <td style="padding:0 0 0.65rem 0;font-size:0.75rem;color:#6c5b32;">
                                    {{ $copy['bancard_rate'] }}
                                </td>
                                <td style="padding:0 0 0.65rem 0;text-align:right;font-size:0.75rem;color:#6c5b32;">
                                    G$ {{ number_format((float) $order->payment_exchange_rate, 2, ',', '.') }} / US$ 1
                                </td>
                            </tr>
                        @endif
                    </table>
                @endif

                <p style="margin:0;font-size:0.82rem;line-height:1.65;color:#4a4029;">
                    {{ $copy['bancard_bank_charges'] }}
                </p>
            </td>
        </tr>
    </table>
@endif
