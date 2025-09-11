@extends('layout.dashboard')

@section('content')
    <h2 class="mb-3">Histórico de Pedidos</h2>

    @if ($orders->count())
        <ul class="list-group">
            @foreach ($orders as $order)
                <li
                    class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                    <div class="mb-2 mb-md-0">
                        <i class="fas fa-box-open me-2"></i>
                        Pedido #{{ $order->id }} - {{ $order->created_at->format('d/m/Y') }}
                        <br>
                        <small>
                            <i class="fas fa-info-circle me-1"></i>Status:
                            @switch($order->status)
                                @case('pending')
                                    Pendente
                                @break

                                @case('processing')
                                    Em Andamento
                                @break

                                @case('completed')
                                    Completo
                                @break

                                @case('canceled')
                                    Cancelado
                                @break

                                @default
                                    Desconhecido
                            @endswitch
                        </small>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <span
                            class="badge 
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
                </li>
            @endforeach
        </ul>
    @else
        <p>Você ainda não realizou pedidos.</p>
    @endif

    <div class="mt-3">
        <a href="{{ route('user.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
@endsection
