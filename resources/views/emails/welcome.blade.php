@extends('layout.email')

@section('title', 'Bem-vindo à SAX Department!')

@section('content')

    @php
        $locale = $emailLocale ?? 'pt_BR';

        $copy = match ($locale) {
            'en' => [
                'hello' => 'Hello,',
                'title' => 'Welcome to SAX Department',
                'line1' => 'We are very happy to have you with us at SAX Department.',
                'line2' => 'To start enjoying our platform and shopping with us, please confirm your email address using the button below.',
                'cta' => 'Verify Email',
                'ignore' => 'If you did not create an account, just ignore this email.',
            ],
            'es' => [
                'hello' => 'Hola,',
                'title' => 'Bienvenido a SAX Department',
                'line1' => 'Estamos muy felices de tenerte con nosotros en SAX Department.',
                'line2' => 'Para comenzar a aprovechar nuestra plataforma y realizar tus compras, confirma tu correo usando el boton abajo.',
                'cta' => 'Confirmar correo',
                'ignore' => 'Si no creaste una cuenta, ignora este correo.',
            ],
            default => [
                'hello' => 'Olá,',
                'title' => 'Bem-vindo à SAX Department',
                'line1' => 'Estamos muito felizes em ter você conosco na SAX Department.',
                'line2' => 'Para começar a aproveitar nossa plataforma e realizar suas compras, por favor, confirme seu endereço de e-mail clicando no botão abaixo.',
                'cta' => 'Confirmar E-mail',
                'ignore' => 'Se você não criou uma conta, ignore este e-mail.',
            ],
        };
    @endphp

    <p style="margin:0 0 0.6rem 0;font-size:0.76rem;letter-spacing:0.2rem;text-transform:uppercase;color:#8a8a8a;">{{ $copy['hello'] }}</p>
    <h1 style="margin:0 0 0.6rem 0;font-size:2rem;font-weight:900;text-transform:uppercase;letter-spacing:0.07rem;color:#111111;line-height:1.15;">{{ $user->name }}</h1>
    <p style="margin:0 0 1.8rem 0;font-size:0.9rem;letter-spacing:0.1rem;text-transform:uppercase;color:#8b8b8b;">{{ $copy['title'] }}</p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 1.5rem 0;">
        <tr>
            <td style="background:#f4f1ec;border-left:4px solid #000;padding:0.9rem 1rem;">
                <span style="font-size:0.82rem;font-weight:700;color:#222222;text-transform:uppercase;letter-spacing:0.08rem;">SAX Account Activation</span>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 1rem 0;font-size:1rem;color:#333333;line-height:1.75;">
        {{ $copy['line1'] }}
    </p>

    <p style="margin:0 0 1.8rem 0;font-size:1rem;color:#333333;line-height:1.75;">
        {{ $copy['line2'] }}
    </p>

    <x-email-button :url="$verificationUrl">{{ $copy['cta'] }}</x-email-button>

    <p style="margin:1.7rem 0 0 0;font-size:0.85rem;color:#888888;line-height:1.7;">
        {{ $copy['ignore'] }}
    </p>

@endsection
