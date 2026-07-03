<!DOCTYPE html>
<html lang="{{ $emailLocale ?? 'pt-BR' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>@yield('title', 'SAX Department Store')</title>
</head>
<body style="margin:0;padding:0;background:linear-gradient(180deg,#efebe5 0%,#f8f6f2 100%);font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:linear-gradient(180deg,#efebe5 0%,#f8f6f2 100%);">
        <tr>
            <td align="center" style="padding:2.5rem 1rem;">

                <table width="700" cellpadding="0" cellspacing="0" border="0" style="max-width:700px;width:100%;">

                    @php
                        $logoRel = 'storage/images/email_header_logo.png';
                    @endphp
                    <tr>
                        <td style="background-color:#000000;padding:0;text-align:center;border-radius:12px 12px 0 0;">
                            @if (file_exists(public_path($logoRel)))
                                <img src="{{ asset($logoRel) }}"
                                     alt="SAX"
                                     width="700"
                                     style="width:100%;max-width:700px;height:auto;display:block;border-radius:12px 12px 0 0;">
                            @else
                                <p style="margin:0;padding:2.2rem 0;color:#ffffff;font-size:2.1rem;font-weight:900;letter-spacing:0.5rem;">SAX</p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#ffffff;padding:3rem 2.4rem;box-shadow:0 18px 32px rgba(0,0,0,0.06);">
                            @yield('content')
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#eeebe7;padding:1.75rem;text-align:center;border-radius:0 0 12px 12px;">
                            <p style="margin:0;font-size:0.8rem;color:#777777;letter-spacing:0.12rem;">SAX Department Store</p>
                            <p style="margin:0.4rem 0 0 0;font-size:0.72rem;color:#9b9b9b;">Ciudad del Este, Paraguai &bull; Foz do Iguacu, Brasil</p>
                            <p style="margin:0.55rem 0 0 0;font-size:0.72rem;color:#b3b3b3;">&copy; {{ date('Y') }} SAX. Todos os direitos reservados.</p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
