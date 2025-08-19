@extends('layout.layout')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        @include('users.components.menu')
    </div>

    <div class="col-md-9">
        <h1 class="mb-3">Bem-vindo, {{ auth()->user()->name }}!</h1>

        <p>Seus dados completos:</p>
        <ul class="list-group mb-4">
            @if(auth()->user()->name)
            <li class="list-group-item"><i class="fas fa-user me-2"></i>Nome: {{ auth()->user()->name }}</li>
            @endif

            @if(auth()->user()->email)
            <li class="list-group-item"><i class="fas fa-envelope me-2"></i>Email: {{ auth()->user()->email }}</li>
            @endif

            @if(auth()->user()->phone_country || auth()->user()->phone_number)
            <li class="list-group-item"><i class="fas fa-phone me-2"></i>Telefone: {{ auth()->user()->phone_country ?? '' }} {{ auth()->user()->phone_number ?? '' }}</li>
            @endif

            @if(auth()->user()->address)
            <li class="list-group-item"><i class="fas fa-home me-2"></i>Endereço: {{ auth()->user()->address }}</li>
            @endif

            @if(auth()->user()->cep)
            <li class="list-group-item"><i class="fas fa-map-pin me-2"></i>CEP: {{ auth()->user()->cep }}</li>
            @endif

            @if(auth()->user()->state)
            <li class="list-group-item"><i class="fas fa-flag me-2"></i>Estado: {{ auth()->user()->state }}</li>
            @endif

            @if(auth()->user()->city)
            <li class="list-group-item"><i class="fas fa-city me-2"></i>Cidade: {{ auth()->user()->city }}</li>
            @endif

            @if(auth()->user()->additional_info)
            <li class="list-group-item"><i class="fas fa-id-card me-2"></i>Número do Cadastro: {{ auth()->user()->additional_info }}</li>
            @endif
        </ul>

        <h2 class="mb-3">Seus Pedidos</h2>
        @if($orders->count())
        <ul class="list-group">
            @foreach($orders as $order)
            <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                <div class="mb-2 mb-md-0">
                    <i class="fas fa-box-open me-2"></i>Pedido #{{ $order->id }} - {{ $order->created_at->format('d/m/Y') }}
                    <br>
                    <small><i class="fas fa-info-circle me-1"></i>Status:
                        @switch($order->status)
                        @case('pending') Pendente @break
                        @case('processing') Em Andamento @break
                        @case('completed') Completo @break
                        @case('canceled') Cancelado @break
                        @default Desconhecido
                        @endswitch
                    </small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge 
                        @switch($order->payment_method)
                            @case('whatsapp') bg-success @break
                            @case('bancard') bg-primary @break
                            @case('deposito') bg-warning @break
                            @default bg-secondary
                        @endswitch">
                        {{ ucfirst($order->payment_method) }}
                    </span>
                    <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i>Ver Detalhes</a>
                </div>
            </li>
            @endforeach
        </ul>
        @else
        <p>Você ainda não realizou pedidos.</p>
        @endif
    </div>
</div>
@endsection
