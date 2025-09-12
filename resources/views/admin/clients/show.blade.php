@extends('layout.admin')

@section('content')
    <div class="container mt-4">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
            <h2>Detalhes do Cliente</h2>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary mt-2 mt-md-0">
                <i class="fa fa-arrow-left me-1"></i> Voltar
            </a>
        </div>

        {{-- Card de informações do cliente --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row gy-3">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted"><i class="fa fa-hashtag me-1"></i> ID</p>
                        <h6>{{ $client->id }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted"><i class="fa fa-user me-1"></i> Nome</p>
                        <h6>{{ $client->name }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted"><i class="fa fa-envelope me-1"></i> Email</p>
                        <h6>{{ $client->email }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted"><i class="fa fa-calendar-alt me-1"></i> Data de Cadastro</p>
                        <h6>{{ $client->created_at->format('d/m/Y H:i') }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-muted"><i class="fa fa-id-badge me-1"></i> Tipo</p>
                        <h6>
                            @switch($client->user_type)
                                @case(1)
                                    <i class="fa fa-user me-1"></i> Cliente
                                @break

                                @case(2)
                                    <i class="fa fa-user-shield me-1"></i> Admin
                                @break

                                @case(3)
                                    <i class="fa fa-graduation-cap me-1"></i> Curso
                                @break

                                @default
                                    <i class="fa fa-question-circle me-1"></i> Desconhecido
                            @endswitch
                        </h6>
                    </div>

                    @if ($client->phone_country || $client->phone_number)
                        <div class="col-md-6">
                            <p class="mb-1 text-muted"><i class="fa fa-phone me-1"></i> Telefone</p>
                            <h6>{{ $client->phone_country ?? '' }} {{ $client->phone_number ?? '' }}</h6>
                        </div>
                    @endif

                    @if ($client->address)
                        <div class="col-md-6">
                            <p class="mb-1 text-muted"><i class="fa fa-home me-1"></i> Endereço</p>
                            <h6>{{ $client->address }}</h6>
                        </div>
                    @endif

                    @if ($client->cep)
                        <div class="col-md-6">
                            <p class="mb-1 text-muted"><i class="fa fa-map-pin me-1"></i> CEP</p>
                            <h6>{{ $client->cep }}</h6>
                        </div>
                    @endif

                    @if ($client->state)
                        <div class="col-md-6">
                            <p class="mb-1 text-muted"><i class="fa fa-flag me-1"></i> Estado</p>
                            <h6>{{ $client->state }}</h6>
                        </div>
                    @endif

                    @if ($client->city)
                        <div class="col-md-6">
                            <p class="mb-1 text-muted"><i class="fa fa-city me-1"></i> Cidade</p>
                            <h6>{{ $client->city }}</h6>
                        </div>
                    @endif

                    @if ($client->additional_info)
                        <div class="col-md-6">
                            <p class="mb-1 text-muted"><i class="fa fa-id-card me-1"></i> Número do Cadastro</p>
                            <h6>{{ $client->additional_info }}</h6>
                        </div>
                    @endif

                    @if ($client->document)
                        <div class="col-md-6">
                            <p class="mb-1 text-muted"><i class="fa fa-id-card me-1"></i> Documento</p>
                            <h6>{{ $client->document }}</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>


        {{-- Pedidos do cliente --}}
        <h3 class="mt-4 mb-3">Pedidos do Cliente</h3>

        @if ($client->orders && $client->orders->count())
            <div class="row g-3">
                @foreach ($client->orders as $order)
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa fa-hashtag me-1"></i> Pedido #{{ $order->id }}
                                </h5>
                                <p class="mb-2">
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="badge bg-warning text-dark"><i class="fa fa-hourglass-half me-1"></i>
                                                Pendente</span>
                                        @break

                                        @case('processing')
                                            <span class="badge bg-info text-dark"><i class="fa fa-spinner me-1"></i> Em
                                                Andamento</span>
                                        @break

                                        @case('completed')
                                            <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> Completo</span>
                                        @break

                                        @case('canceled')
                                            <span class="badge bg-danger"><i class="fa fa-times-circle me-1"></i> Cancelado</span>
                                        @break

                                        @default
                                            <span class="badge bg-secondary"><i class="fa fa-question-circle me-1"></i>
                                                Desconhecido</span>
                                    @endswitch
                                </p>
                                <p class="mb-1"><i class="fa fa-dollar-sign me-1"></i> <strong>R$
                                        {{ number_format($order->total, 2, ',', '.') }}</strong></p>
                                <p class="text-muted mb-3"><i class="fa fa-calendar-alt me-1"></i>
                                    {{ $order->created_at->format('d/m/Y H:i') }}</p>
                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-eye me-1"></i> Ver Pedido
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info mt-3">
                <i class="fa fa-info-circle me-1"></i> Este cliente ainda não realizou nenhum pedido.
            </div>
        @endif
    </div>
@endsection
