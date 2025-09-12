@extends('layout.dashboard')

@section('content')

<h1 class="mb-4">Bem-vindo, {{ auth()->user()->name }}!</h1>

{{-- Dados do usuário --}}
<div class="row g-3 mb-4">
    @if(auth()->user()->name)
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-user fa-2x"></i>
                    <div>
                        <small class="text-muted">Nome</small>
                        <div>{{ auth()->user()->name }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->email)
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-envelope fa-2x"></i>
                    <div>
                        <small class="text-muted">Email</small>
                        <div>{{ auth()->user()->email }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->phone_country || auth()->user()->phone_number)
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-phone fa-2x"></i>
                    <div>
                        <small class="text-muted">Telefone</small>
                        <div>{{ auth()->user()->phone_country ?? '' }} {{ auth()->user()->phone_number ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->address)
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-home fa-2x"></i>
                    <div>
                        <small class="text-muted">Endereço</small>
                        <div>{{ auth()->user()->address }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->cep)
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-map-pin fa-2x"></i>
                    <div>
                        <small class="text-muted">CEP</small>
                        <div>{{ auth()->user()->cep }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->state)
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-flag fa-2x"></i>
                    <div>
                        <small class="text-muted">Estado</small>
                        <div>{{ auth()->user()->state }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->city)
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-city fa-2x"></i>
                    <div>
                        <small class="text-muted">Cidade</small>
                        <div>{{ auth()->user()->city }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->additional_info)
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-id-card fa-2x"></i>
                    <div>
                        <small class="text-muted">Número do Cadastro</small>
                        <div>{{ auth()->user()->additional_info }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()->document)
        <div class="col-12 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center gap-2">
                    <i class="fas fa-id-card fa-2x"></i>
                    <div>
                        <small class="text-muted">Documento</small>
                        <div>{{ auth()->user()->document }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Pedidos recentes --}}
<h2 class="mb-3">Seus Pedidos</h2>

@if ($orders->count())
    <div class="row g-3">
        @foreach ($orders->take(5) as $order)
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                        <div>
                            <i class="fas fa-box-open me-2"></i>
                            Pedido #{{ $order->id }} - {{ $order->created_at->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">
                                Status:
                                @switch($order->status)
                                    @case('pending') Pendente @break
                                    @case('processing') Em Andamento @break
                                    @case('completed') Completo @break
                                    @case('canceled') Cancelado @break
                                    @default Desconhecido
                                @endswitch
                            </small>
                        </div>
                        <div class="d-flex gap-2 flex-wrap mt-2 mt-md-0">
                            <span class="badge
                                @switch($order->payment_method)
                                    @case('whatsapp') bg-success @break
                                    @case('bancard') bg-primary @break
                                    @case('deposito') bg-warning @break
                                    @default bg-secondary
                                @endswitch">
                                {{ ucfirst($order->payment_method) }}
                            </span>
                            <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i> Ver Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3 text-center">
        <a href="{{ route('user.orders') }}" class="btn btn-outline-primary">
            <i class="fas fa-history me-1"></i> Histórico de Pedidos
        </a>
    </div>
@else
    <p>Você ainda não realizou pedidos.</p>
@endif

@endsection
