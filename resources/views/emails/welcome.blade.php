@extends('layout.email')

@section('title', 'Bem-vindo à SAX Department!')

@section('content')

    <p style="margin:0 0 0.5rem 0;font-size:0.75rem;letter-spacing:0.2rem;text-transform:uppercase;color:#888888;">Olá,</p>
    <h1 style="margin:0 0 1.5rem 0;font-size:1.75rem;font-weight:900;text-transform:uppercase;letter-spacing:0.1rem;color:#000000;">{{ $user->name }}</h1>

    <p style="margin:0 0 1rem 0;font-size:0.9375rem;color:#333333;line-height:1.6;">
        Estamos muito felizes em ter você conosco na <strong>SAX Department</strong>.
    </p>

    <p style="margin:0 0 1.5rem 0;font-size:0.9375rem;color:#333333;line-height:1.6;">
        Para começar a aproveitar nossa plataforma e realizar suas compras, por favor, confirme seu endereço de e-mail clicando no botão abaixo.
    </p>

    <x-email-button :url="$verificationUrl">Confirmar E-mail</x-email-button>

    <p style="margin:1.5rem 0 0 0;font-size:0.8125rem;color:#888888;line-height:1.6;">
        Se você não criou uma conta, ignore este e-mail.
    </p>

@endsection
