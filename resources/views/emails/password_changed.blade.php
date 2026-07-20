@extends('layout.email')

@section('title', 'Sua senha foi alterada')

@section('content')
    <p style="margin:0 0 0.6rem;font-size:0.76rem;letter-spacing:0.2rem;text-transform:uppercase;color:#8a8a8a;">
        Segurança da conta
    </p>

    <h1 style="margin:0 0 1.5rem;font-size:1.8rem;font-weight:900;text-transform:uppercase;letter-spacing:0.05rem;color:#111;line-height:1.2;">
        Sua senha foi alterada
    </h1>

    <p style="margin:0 0 1.4rem;font-size:1rem;color:#2f2f2f;line-height:1.75;">
        Olá, {{ $user->name }}. Confirmamos que a senha da sua conta SAX foi alterada com sucesso.
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 1.5rem;background:#f4f1ec;border-left:4px solid #000;">
        <tr>
            <td style="padding:1rem 1.1rem;">
                <span style="display:block;margin-bottom:0.25rem;font-size:0.68rem;text-transform:uppercase;letter-spacing:0.12rem;color:#888;">Data e hora</span>
                <strong style="font-size:0.95rem;color:#111;">{{ $changedAt->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</strong>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 1.4rem;font-size:0.92rem;color:#444;line-height:1.7;">
        Se você realizou esta alteração, nenhuma outra ação é necessária. Por segurança, sua senha nunca é enviada por e-mail.
    </p>

    <p style="margin:0;font-size:0.92rem;font-weight:700;color:#111;line-height:1.7;">
        Não reconhece esta alteração? Acesse imediatamente a recuperação de senha ou entre em contato com o atendimento SAX.
    </p>
@endsection
