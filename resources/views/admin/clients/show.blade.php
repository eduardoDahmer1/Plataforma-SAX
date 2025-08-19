@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <h2>Detalhes do Cliente</h2>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary mt-2 mt-md-0">
            <i class="fa fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered mt-3">
            <tr>
                <th><i class="fa fa-hashtag me-1"></i> ID</th>
                <td>{{ $client->id }}</td>
            </tr>
            <tr>
                <th><i class="fa fa-user me-1"></i> Nome</th>
                <td>{{ $client->name }}</td>
            </tr>
            <tr>
                <th><i class="fa fa-envelope me-1"></i> Email</th>
                <td>{{ $client->email }}</td>
            </tr>
            <tr>
                <th><i class="fa fa-calendar-alt me-1"></i> Data de Cadastro</th>
                <td>{{ $client->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th><i class="fa fa-id-badge me-1"></i> Tipo</th>
                <td>
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
                </td>
            </tr>
        </table>
    </div>

    {{-- Pedidos do cliente --}}
    <h3 class="mt-5 mb-3">Pedidos do Cliente</h3>

    <div class="table-responsive">
        @if($client->orders && $client->orders->count())
            <table class="table table-striped table-bordered mt-2">
                <thead>
                    <tr>
                        <th><i class="fa fa-hashtag me-1"></i> ID Pedido</th>
                        <th><i class="fa fa-info-circle me-1"></i> Status</th>
                        <th><i class="fa fa-dollar-sign me-1"></i> Total</th>
                        <th><i class="fa fa-calendar-alt me-1"></i> Data</th>
                        <th><i class="fa fa-cogs me-1"></i> Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>
                                @switch($order->status)
                                    @case('pending')
                                        <span class="badge bg-warning text-dark"><i class="fa fa-hourglass-half me-1"></i> Pendente</span>
                                    @break
                                    @case('processing')
                                        <span class="badge bg-info text-dark"><i class="fa fa-spinner me-1"></i> Em Andamento</span>
                                    @break
                                    @case('completed')
                                        <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> Completo</span>
                                    @break
                                    @case('canceled')
                                        <span class="badge bg-danger"><i class="fa fa-times-circle me-1"></i> Cancelado</span>
                                    @break
                                    @default
                                        <span class="badge bg-secondary"><i class="fa fa-question-circle me-1"></i> Desconhecido</span>
                                @endswitch
                            </td>
                            <td>R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                    <i class="fa fa-eye me-1"></i> Ver Pedido
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Este cliente ainda não realizou nenhum pedido.</p>
        @endif
    </div>
</div>
@endsection
