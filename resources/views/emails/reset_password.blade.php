@extends('layout.email')

@section('title', 'Recuperação de Senha')

@section('content')

    <p style="margin:0 0 0.5rem 0;font-size:0.75rem;letter-spacing:0.2rem;text-transform:uppercase;color:#888888;">Olá!</p>
    <h1 style="margin:0 0 1.5rem 0;font-size:1.75rem;font-weight:900;text-transform:uppercase;letter-spacing:0.1rem;color:#000000;">Redefinição de Senha</h1>

    <p style="margin:0 0 1rem 0;font-size:0.9375rem;color:#333333;line-height:1.6;">
        Você está recebendo este e-mail porque solicitou a redefinição de senha da sua conta.
    </p>

    <p style="margin:0 0 1.5rem 0;font-size:0.9375rem;color:#333333;line-height:1.6;">
        Clique no botão abaixo para criar uma nova senha. Este link expirará em <strong>{{ $expireMinutes }} minutos</strong>.
    </p>

    <x-email-button :url="$resetUrl">Redefinir Senha</x-email-button>

    <p style="margin:1.5rem 0 0 0;font-size:0.8125rem;color:#888888;line-height:1.6;">
        Se você não solicitou a redefinição de senha, ignore este e-mail — sua conta continua segura.
    </p>

@endsection
