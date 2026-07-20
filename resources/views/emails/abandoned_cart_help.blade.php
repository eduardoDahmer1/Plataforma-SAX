@extends('layout.email')

@section('title', 'Podemos ajudar com sua compra?')

@section('content')
    <p style="font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#777">Atendimento SAX</p>
    <h1 style="font-size:28px;color:#111">Ficou alguma dúvida?</h1>
    <p style="font-size:16px;line-height:1.7;color:#333">Olá, {{ $cart->user?->name }}. Vimos que você decidiu não continuar com seu carrinho agora. Queremos entender se podemos ajudar, sem compromisso.</p>
    <p style="font-size:15px;color:#444">Selecione a opção que melhor explica:</p>
    @php
        $options = [
            'later' => 'Vou comprar em outro momento',
            'payment' => 'Não consegui realizar o pagamento',
            'help' => 'Preciso de ajuda para comprar',
            'gave_up' => 'Não quero mais os produtos',
            'other' => 'Outro motivo',
        ];
    @endphp
    @foreach($options as $reason => $label)
        <p style="margin:8px 0"><a href="{{ route('abandoned-cart.feedback', ['token' => $cart->recovery_token, 'reason' => $reason]) }}" style="display:block;padding:12px 15px;background:#f3f4f6;color:#111;text-decoration:none;border-left:4px solid #111">{{ $label }}</a></p>
    @endforeach
    <p style="margin-top:24px;font-size:14px;color:#666">Seu carrinho ficou salvo no seu histórico e poderá ser restaurado enquanto os produtos estiverem disponíveis.</p>
@endsection
