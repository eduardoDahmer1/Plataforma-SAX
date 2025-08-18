@extends('layout.layout')

@section('content')
<div class="row">
    <div class="col-md-3">
        @include('users.components.menu')
    </div>
    <div class="col-md-9">
        <h1>Bem-vindo, {{ auth()->user()->name }}!</h1>

        <p>Seus dados completos:</p>
        <ul>
            @if(auth()->user()->name)
            <li>Nome: {{ auth()->user()->name }}</li>
            @endif

            @if(auth()->user()->email)
            <li>Email: {{ auth()->user()->email }}</li>
            @endif

            @if(auth()->user()->phone_country || auth()->user()->phone_number)
            <li>Telefone: {{ auth()->user()->phone_country ?? '' }} {{ auth()->user()->phone_number ?? '' }}</li>
            @endif

            @if(auth()->user()->address)
            <li>Endereço: {{ auth()->user()->address }}</li>
            @endif

            @if(auth()->user()->cep)
            <li>CEP: {{ auth()->user()->cep }}</li>
            @endif

            @if(auth()->user()->state)
            <li>Estado: {{ auth()->user()->state }}</li>
            @endif

            @if(auth()->user()->city)
            <li>Cidade: {{ auth()->user()->city }}</li>
            @endif

            @if(auth()->user()->additional_info)
            <li>Numero do Cadastro: {{ auth()->user()->additional_info }}</li>
            @endif
        </ul>

        <h2>Seus Pedidos</h2>
        @if($orders->count())
        <ul class="list-group">
            @foreach($orders as $order)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    Pedido #{{ $order->id }} - {{ $order->created_at->format('d/m/Y') }}
                    <br>
                    <small>Status:
                        @switch($order->status)
                        @case('pending') Pendente @break
                        @case('processing') Em Andamento @break
                        @case('completed') Completo @break
                        @case('canceled') Cancelado @break
                        @default Desconhecido
                        @endswitch
                    </small>
                </div>
                <span class="badge 
            @switch($order->payment_method)
                @case('whatsapp') bg-success @break
                @case('bancard') bg-primary @break
                @case('deposito') bg-warning @break
                @default bg-secondary
            @endswitch">
                    {{ ucfirst($order->payment_method) }}
                </span>
                <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-sm btn-primary">Ver Detalhes</a>
            </li>
            @endforeach
        </ul>
        @else
        <p>Você ainda não realizou pedidos.</p>
        @endif
    </div>
</div>
@endsection