<!DOCTYPE html>
<html lang="{{ $emailLocale ?? 'pt-BR' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light">
    <title>@yield('title', 'SAX Department Store')</title>
    <style>
        :root { color-scheme: light only; }
        body, table, td, a, p, h1, h2 { font-family:Arial,'Helvetica Neue',Helvetica,sans-serif; }
        img { border:0; display:block; height:auto; line-height:100%; outline:none; text-decoration:none; }
        table { border-collapse:collapse; }
        a { color:#25282c; }
        .sax-email-shell { width:100%; background:#f3f3f1; }
        .sax-email-frame { width:100%; max-width:620px; }
        .sax-email-body { padding:28px 30px; }
        @media only screen and (max-width:640px) {
            .sax-email-outside { padding:12px 8px !important; }
            .sax-email-body { padding:22px 18px !important; }
            .sax-email-brand { padding:15px 18px !important; }
            .sax-email-footer { padding:18px !important; }
            .sax-email-title { font-size:23px !important; line-height:1.2 !important; }
            .sax-email-copy { font-size:14px !important; line-height:1.6 !important; }
            .sax-email-card-cell { padding:14px !important; }
            .sax-email-action a { display:block !important; padding:13px 16px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f3f3f1;color:#25282c;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="sax-email-shell" style="width:100%;background:#f3f3f1;">
        <tr>
            <td class="sax-email-outside" align="center" style="padding:28px 12px;">
                <table role="presentation" width="620" cellpadding="0" cellspacing="0" border="0" class="sax-email-frame" style="width:100%;max-width:620px;">
                    <tr>
                        <td class="sax-email-brand" style="padding:17px 24px;background:#303236;border:1px solid #303236;border-radius:5px 5px 0 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="color:#ffffff;font-size:18px;font-weight:800;letter-spacing:.12em;line-height:1;">SAX</td>
                                    <td align="right" style="color:#c8cacc;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Department Store</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="sax-email-body" style="padding:28px 30px;background:#ffffff;border-right:1px solid #d7d8da;border-left:1px solid #d7d8da;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td class="sax-email-footer" style="padding:20px 24px;background:#ececeb;border:1px solid #d7d8da;border-radius:0 0 5px 5px;text-align:center;">
                            <p style="margin:0;color:#45494e;font-size:11px;font-weight:700;">SAX Department Store</p>
                            <p style="margin:5px 0 0;color:#74787d;font-size:10px;line-height:1.5;">Ciudad del Este, Paraguai &bull; Foz do Iguaçu, Brasil</p>
                            <p style="margin:5px 0 0;color:#8a8d91;font-size:10px;">&copy; {{ date('Y') }} SAX. Todos os direitos reservados.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
