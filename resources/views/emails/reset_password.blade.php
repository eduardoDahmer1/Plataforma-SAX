@extends('layout.email')

@section('title', 'Recuperação de Senha')

@section('content')

    @php
        $locale = $emailLocale ?? 'pt_BR';

        $copy = match ($locale) {
            'en' => [
                'hello' => 'Hello!',
                'title' => 'Password Reset',
                'line1' => 'You are receiving this email because a password reset was requested for your account.',
                'line2' => 'Click the button below to create a new password. This link will expire in',
                'minutes' => 'minutes',
                'cta' => 'Reset Password',
                'ignore' => 'If you did not request a password reset, just ignore this email. Your account remains secure.',
                'security' => 'Security notice',
            ],
            'es' => [
                'hello' => 'Hola!',
                'title' => 'Restablecer contrasena',
                'line1' => 'Recibes este correo porque se solicito un restablecimiento de contrasena para tu cuenta.',
                'line2' => 'Haz clic en el boton abajo para crear una nueva contrasena. Este enlace expirara en',
                'minutes' => 'minutos',
                'cta' => 'Restablecer contrasena',
                'ignore' => 'Si no solicitaste el restablecimiento de contrasena, ignora este correo. Tu cuenta sigue segura.',
                'security' => 'Aviso de seguridad',
            ],
            default => [
                'hello' => 'Olá!',
                'title' => 'Redefinição de Senha',
                'line1' => 'Você está recebendo este e-mail porque solicitou a redefinição de senha da sua conta.',
                'line2' => 'Clique no botão abaixo para criar uma nova senha. Este link expirará em',
                'minutes' => 'minutos',
                'cta' => 'Redefinir Senha',
                'ignore' => 'Se você não solicitou a redefinição de senha, ignore este e-mail. Sua conta continua segura.',
                'security' => 'Aviso de segurança',
            ],
        };
    @endphp

    <p style="margin:0 0 0.6rem 0;font-size:0.76rem;letter-spacing:0.2rem;text-transform:uppercase;color:#8a8a8a;">{{ $copy['hello'] }}</p>
    <h1 style="margin:0 0 1.8rem 0;font-size:2rem;font-weight:900;text-transform:uppercase;letter-spacing:0.07rem;color:#111111;line-height:1.15;">{{ $copy['title'] }}</h1>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 1.5rem 0;">
        <tr>
            <td style="background:#f4f1ec;border-left:4px solid #000;padding:0.9rem 1rem;">
                <span style="font-size:0.82rem;font-weight:700;color:#222222;text-transform:uppercase;letter-spacing:0.08rem;">{{ $copy['security'] }}</span>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 1rem 0;font-size:1rem;color:#333333;line-height:1.75;">
        {{ $copy['line1'] }}
    </p>

    <p style="margin:0 0 1.8rem 0;font-size:1rem;color:#333333;line-height:1.75;">
        {{ $copy['line2'] }} <strong>{{ $expireMinutes }} {{ $copy['minutes'] }}</strong>.
    </p>

    <x-email-button :url="$resetUrl">{{ $copy['cta'] }}</x-email-button>

    <p style="margin:1.7rem 0 0 0;font-size:0.85rem;color:#888888;line-height:1.7;">
        {{ $copy['ignore'] }}
    </p>

@endsection
