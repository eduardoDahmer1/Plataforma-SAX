@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Detalhes do Cliente</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item small"><a href="{{ route('admin.clients.index') }}">Clientes</a></li>
                    <li class="breadcrumb-item active small">{{ $client->name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-dark rounded-0 px-4">
            <i class="fas fa-arrow-left me-2"></i> VOLTAR
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-4">
            <div class="card h-100 rounded-0 border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                        {{ substr($client->name, 0, 1) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $client->name }}</h5>
                    <p class="text-muted small mb-4">{{ $client->email }}</p>
                    
                    <div class="border-top pt-3 text-start">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small fw-bold">ID USUÁRIO</span>
                            <span class="fw-bold">#{{ $client->id }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small fw-bold">DATA CADASTRO</span>
                            <span>{{ $client->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small fw-bold">TIPO</span>
                            {{-- user_type 1 é o administrador (ver middleware CheckAdmin) --}}
                            <span class="badge {{ $client->isAdmin() ? 'bg-dark' : 'bg-light text-dark border' }}">
                                {{ $client->user_role }}
                            </span>
                        </div>

                        @php
                            $pedidosPagos = $client->orders->where('payment_status', 'paid');
                            $totalGasto = $pedidosPagos->sum(fn ($o) => (float) $o->total);
                            $totalEconomizado = $client->orders->sum(fn ($o) => (float) ($o->discount ?? 0));
                        @endphp

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small fw-bold">TOTAL COMPRADO</span>
                            {{-- Os totais são gravados na moeda base (USD) --}}
                            <span class="fw-bold">US$ {{ number_format($totalGasto, 2, '.', ',') }}</span>
                        </div>

                        @if ($totalEconomizado > 0)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small fw-bold">DESCONTOS</span>
                                <span class="fw-bold text-success">US$ {{ number_format($totalEconomizado, 2, '.', ',') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100 rounded-0 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-uppercase m-0 text-secondary" style="font-size: 0.8rem;">Informações de Perfil</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        @php
                            // O endereço do usuário é guardado em address/number/district/complement.
                            $logradouro = trim(collect([
                                trim(($client->address ?? '') . ' ' . ($client->number ?? '')),
                                $client->complement,
                                $client->district,
                            ])->filter()->implode(', '));

                            $telefone = trim(($client->phone_country ? '+' . $client->phone_country : '') . ' ' . ($client->phone_number ?? ''));

                            $fields = [
                                'Telefone' => $telefone,
                                'E-mail' => $client->email,
                                'Documento' => $client->document,
                                'Endereço' => $logradouro,
                                'Cidade' => $client->city,
                                'Estado' => $client->state,
                                'País' => $client->country,
                                'CEP' => $client->cep,
                                'Informações Adicionais' => $client->additional_info,
                            ];
                        @endphp
                        @foreach($fields as $label => $value)
                            @if($value && trim($value) !== '')
                                <div class="col-md-6">
                                    <label class="d-block text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">{{ $label }}</label>
                                    <div class="fw-semibold text-dark">{{ $value }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold m-0">Histórico de Pedidos</h5>
        <span class="badge bg-dark">{{ $client->orders->count() }} pedidos encontrados</span>
    </div>

    <div class="card rounded-0 border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle m-0">
                <thead class="bg-light">
                    <tr class="text-uppercase text-muted" style="font-size: 0.75rem;">
                        <th class="py-3 ps-4">Pedido</th>
                        <th class="py-3">Data</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Pagamento</th>
                        <th class="py-3">Cupom</th>
                        <th class="py-3 text-end">Total</th>
                        <th class="py-3 text-end pe-4">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($client->orders as $order)
                        @php
                            $statusColors = [
                                'pending' => 'bg-warning text-dark',
                                'processing' => 'bg-info text-dark',
                                'shipped' => 'bg-primary text-white',
                                'completed' => 'bg-success text-white',
                                'canceled' => 'bg-danger text-white',
                            ];
                            $corPagamento = match ($order->payment_status) {
                                'paid' => 'text-success',
                                'failed' => 'text-danger',
                                'refunded' => 'text-secondary',
                                default => 'text-warning',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4 fw-bold">#{{ $order->order_number ?? $order->id }}</td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $statusColors[$order->status] ?? 'bg-secondary' }}">
                                    {{ __('messages.status_' . $order->status) }}
                                </span>
                            </td>
                            <td class="small {{ $corPagamento }}">
                                {{ __('messages.payment_status_' . $order->payment_status) }}
                            </td>
                            <td>
                                @if ($order->cupon)
                                    <span class="badge bg-light text-dark border">{{ $order->cupon->codigo }}</span>
                                    <span class="d-block x-small text-success">- {{ order_money($order, $order->discount) }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            {{-- Na moeda em que o cliente fechou o pedido, não em R$ fixo --}}
                            <td class="fw-bold text-end">{{ order_money($order, $order->total) }}</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-0">
                                    <i class="fa fa-eye me-1"></i> Visualizar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Nenhum pedido realizado por este cliente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin.card>
@endsection