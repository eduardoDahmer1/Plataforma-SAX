<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SAX Department Store')</title>
</head>
<body style="margin:0;padding:0;background-color:#f0ece6;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0ece6;">
        <tr>
            <td align="center" style="padding:2rem 1rem;">

                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

                    {{-- Header negro: logo si existe, fallback de texto SAX si no --}}
                    <tr>
                        <td style="background-color:#000000;padding:2rem;text-align:center;">
                            @if (!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="SAX" style="max-height:3rem;width:auto;display:block;margin:0 auto;filter:invert(1);">
                            @else
                                <p style="margin:0;color:#ffffff;font-size:2rem;font-weight:900;letter-spacing:0.5rem;">SAX</p>
                                <p style="margin:0.25rem 0 0 0;color:#cccccc;font-size:0.625rem;letter-spacing:0.3rem;">STYLE &bull; ARTS &bull; XTRAS</p>
                            @endif
                        </td>
                    </tr>

                    {{-- Cuerpo blanco: cada email define su propio contenido --}}
                    <tr>
                        <td style="background-color:#ffffff;padding:2.5rem 2rem;">
                            @yield('content')
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
