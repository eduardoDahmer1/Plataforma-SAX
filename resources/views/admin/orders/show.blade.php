@extends('layout.admin')

@section('content')
    <div class="container mt-4">

        {{-- Cabeçalho --}}
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <h2>Detalhes do Pedido #{{ $order->id }}</h2>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> Voltar
            </a>
        </div>

        {{-- DADOS DO CLIENTE --}}
        <section class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-user me-2"></i>Dados do Cliente</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold text-secondary">Cliente:</div>
                    <div class="col-md-9">{{ $order->user->name ?? ($order->name ?? 'Cliente') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold text-secondary">Documento:</div>
                    <div class="col-md-9">{{ $order->user->document ?? ($order->document ?? '-') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold text-secondary">Email:</div>
                    <div class="col-md-9">{{ $order->user->email ?? ($order->email ?? '-') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold text-secondary">Telefone:</div>
                    <div class="col-md-9">{{ $order->user->phone_number ?? ($order->phone ?? '-') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold text-secondary">Status:</div>
                    <div class="col-md-9">
                        <form action="{{ route('admin.orders.update', $order->id) }}" method="POST"
                            class="d-flex flex-column flex-md-row gap-2">
                            @csrf
                            @method('PUT')
                            <select name="status" class="form-control flex-fill">
                                @foreach (['pending' => 'Pendente', 'processing' => 'Em Andamento', 'completed' => 'Completo', 'canceled' => 'Cancelado'] as $key => $label)
                                    <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary flex-md-shrink-0">
                                <i class="fa fa-save me-1"></i> Atualizar
                            </button>
                        </form>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold text-secondary">Data do Pedido:</div>
                    <div class="col-md-9">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold text-secondary">Total:</div>
                    <div class="col-md-9">
                        @php $total = $order->items->sum(fn($item)=>$item->price*$item->quantity); @endphp
                        {{ currency_format($total) }}
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3 fw-bold text-secondary">Método de Pagamento:</div>
                    <div class="col-md-9">
                        @switch($order->payment_method)
                            @case('bancard')
                                <i class="fa fa-credit-card me-1 text-primary"></i> Bancard
                            @break

                            @case('deposito')
                                <i class="fa fa-university me-1 text-primary"></i> Depósito
                            @break

                            @case('whatsapp')
                                <i class="fa fa-whatsapp me-1 text-success"></i> WhatsApp
                            @break

                            @default
                                Não informado
                        @endswitch
                    </div>
                </div>
                @if ($order->deposit_receipt)
                    <div class="row mb-2">
                        <div class="col-md-3 fw-bold text-secondary">Comprovante:</div>
                        <div class="col-md-9">
                            <a href="{{ asset('storage/' . $order->deposit_receipt) }}" target="_blank">
                                <img src="{{ asset('storage/' . $order->deposit_receipt) }}" alt="Comprovante"
                                    class="img-fluid border rounded" style="max-width:200px;">
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- ITENS DO PEDIDO --}}
        <section class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white d-flex align-items-center">
                <i class="fa fa-boxes me-2"></i>
                <h5 class="mb-0">Itens do Pedido</h5>
            </div>
            <div class="card-body p-0">
                @if ($order->items->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Preço Unitário</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            {{ $item->product->name ?? ($item->product->external_name ?? 'Produto não encontrado') }}
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ currency_format($item->price) }}</td>
                                        <td class="text-end fw-bold">
                                            {{ currency_format($item->quantity * $item->price) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-3 text-muted">Este pedido não possui itens.</div>
                @endif
            </div>
        </section>        

        {{-- DADOS DE CHECKOUT --}}
        <section class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fa fa-receipt me-2"></i>Resumo do Checkout</h5>
            </div>
            <div class="card-body">
                <h5 class="fw-bold mb-3 text-dark">
                    <i class="fas fa-map-marker-alt me-2 text-primary"></i> Endereço de Envio
                </h5>

                <div class="p-3 border rounded-3 mb-3 bg-light">
                    @if ($order->shipping == 1)
                        <p class="mb-1">
                            {{ $order->street ?? ($order->user->street ?? '-') }},
                            Nº {{ $order->number ?? ($order->user->number ?? '-') }}
                        </p>
                        <p class="mb-1">CEP: {{ $order->cep ?? ($order->user->cep ?? '-') }}</p>
                        <p class="mb-1">
                            {{ $order->city ?? ($order->user->city ?? '-') }},
                            {{ $order->state ?? ($order->user->state ?? '-') }}
                        </p>
                        <p class="mb-0">{{ $order->country ?? ($order->user->country ?? '-') }}</p>
                    @elseif($order->shipping == 2)
                        <p class="mb-1">
                            {{ $order->street ?? '-' }},
                            Nº {{ $order->number ?? '-' }}
                        </p>
                        <p class="mb-1">CEP: {{ $order->cep ?? '-' }}</p>
                        <p class="mb-1">
                            {{ $order->city ?? '-' }},
                            {{ $order->state ?? '-' }}
                        </p>
                        <p class="mb-0">{{ $order->country ?? '-' }}</p>
                    @elseif($order->shipping == 3)
                        <p class="mb-0">
                            <span class="badge bg-info text-dark">
                                Retirada na loja
                            </span><br>
                            {{ $order->store == 1 ? 'SAX Ciudad del Este' : 'SAX Assunção' }}
                        </p>
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <h6 class="fw-bold text-secondary mb-1">País</h6>
                            <p class="mb-0">{{ $order->country ?? ($order->user->location_country ?? '-') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <h6 class="fw-bold text-secondary mb-1">Observações</h6>
                            <p class="mb-0">{{ $order->observations ?: '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

    {{-- CSS adicional --}}
    <style>
        .card {
            border-radius: 12px;
        }

        .card-body {
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .fw-bold {
            font-weight: 600;
        }

        .text-secondary {
            color: #6c757d !important;
        }

        .border-bottom {
            border-bottom: 1px solid #dee2e6 !important;
        }
    </style>
@endsection
