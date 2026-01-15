@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Navegação e ID --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-decoration-none x-small fw-bold text-uppercase text-secondary tracking-wider">
                <i class="fa fa-chevron-left me-1"></i> Volver a pedidos
            </a>
            <h1 class="h4 fw-light mt-2 mb-0 text-uppercase tracking-wider">Orden #{{ $order->id }}</h1>
        </div>
        <div class="d-flex gap-2">
            <span class="status-dot {{ $order->status }}"></span>
            <span class="x-small text-uppercase fw-bold text-dark">{{ $order->status }}</span>
        </div>
    </div>

    <div class="row g-5">
        {{-- Coluna Principal: Itens e Resumo --}}
        <div class="col-lg-8">
            
            {{-- Itens do Pedido --}}
            <section class="mb-5">
                <h6 class="x-small fw-bold text-uppercase tracking-wider mb-4 pb-2 border-bottom">Productos</h6>
                @if ($order->items->count())
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="x-small text-secondary text-uppercase border-top-0">
                                <tr>
                                    <th class="border-0 ps-0">Descripción</th>
                                    <th class="border-0 text-center">Cantidad</th>
                                    <th class="border-0 text-end pe-0">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr class="border-bottom-0">
                                        <td class="py-3 ps-0">
                                            <span class="d-block fw-bold text-dark">{{ $item->product->name ?? 'Producto no encontrado' }}</span>
                                            <span class="x-small text-muted italic">Ref: {{ $item->product->id ?? '-' }}</span>
                                        </td>
                                        <td class="py-3 text-center text-secondary">{{ $item->quantity }}</td>
                                        <td class="py-3 text-end pe-0 fw-bold text-dark">
                                            {{ currency_format($item->quantity * $item->price) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small">Sin items registrados.</p>
                @endif
            </section>

            {{-- Checkout e Detalhes de Envio --}}
            <section>
                <h6 class="x-small fw-bold text-uppercase tracking-wider mb-4 pb-2 border-bottom">Logística y Entrega</h6>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="x-small text-secondary text-uppercase fw-bold d-block mb-2">Destino</label>
                        <div class="bg-light-subtle p-3 border-start border-dark border-3">
                            @if ($order->shipping == 3)
                                <p class="mb-0 small fw-bold"><i class="fa fa-store me-1"></i> Retiro en tienda</p>
                                <span class="x-small text-muted">{{ $order->store == 1 ? 'SAX Ciudad del Este' : 'SAX Asunción' }}</span>
                            @else
                                <p class="mb-1 small fw-bold">{{ $order->street ?? '-' }}, {{ $order->number ?? '' }}</p>
                                <p class="mb-0 x-small text-secondary">{{ $order->city ?? '-' }}, {{ $order->state ?? '-' }}</p>
                                <p class="mb-0 x-small text-secondary">CP: {{ $order->cep ?? '-' }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="x-small text-secondary text-uppercase fw-bold d-block mb-2">Observaciones</label>
                        <p class="small text-muted italic">{{ $order->observations ?: 'Sin notas adicionales.' }}</p>
                    </div>
                </div>
            </section>
        </div>

        {{-- Coluna Lateral: Cliente e Ações --}}
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 20px;">
                
                {{-- Card de Status --}}
                <div class="border p-4 mb-4">
                    <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3">Gestión de Pedido</h6>
                    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                        @csrf @method('PUT')
                        <select name="status" class="form-select form-select-sm rounded-0 border-dark-subtle mb-3">
                            @foreach (['pending' => 'Pendiente', 'processing' => 'En Proceso', 'completed' => 'Completado', 'canceled' => 'Cancelado'] as $key => $label)
                                <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-dark w-100 btn-sm rounded-0 text-uppercase fw-bold tracking-wider">
                            Actualizar Estado
                        </button>
                    </form>
                </div>

                {{-- Informações do Cliente --}}
                <div class="border p-4 mb-4 bg-white">
                    <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3 pb-2 border-bottom">Comprador</h6>
                    <div class="mb-3">
                        <span class="d-block small fw-bold">{{ $order->user->name ?? $order->name }}</span>
                        <span class="d-block x-small text-secondary">{{ $order->user->email ?? $order->email }}</span>
                        <span class="d-block x-small text-secondary italic">{{ $order->user->phone_number ?? $order->phone }}</span>
                    </div>
                    <div class="x-small">
                        <span class="text-secondary">Doc:</span> {{ $order->user->document ?? '-' }}
                    </div>
                </div>

                {{-- Resumo Financeiro --}}
                <div class="border p-4 bg-light-subtle">
                    <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3">Resumen de Pago</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="x-small text-secondary text-uppercase">Método</span>
                        <span class="x-small fw-bold">{{ strtoupper($order->payment_method) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="x-small text-secondary text-uppercase">Total</span>
                        <span class="h5 mb-0 fw-bold">
                            @php $total = $order->items->sum(fn($item)=>$item->price*$item->quantity); @endphp
                            {{ currency_format($total) }}
                        </span>
                    </div>

                    @if ($order->deposit_receipt)
                        <div class="pt-3 border-top">
                            <label class="x-small fw-bold text-uppercase d-block mb-2">Comprobante</label>
                            <a href="{{ asset('storage/' . $order->deposit_receipt) }}" target="_blank" class="d-block border overflow-hidden">
                                <img src="{{ asset('storage/' . $order->deposit_receipt) }}" class="img-fluid grayscale-hover transition-all">
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Minimalist Tweaks */
    .tracking-wider { letter-spacing: 0.12em; }
    .x-small { font-size: 0.65rem; }
    .italic { font-style: italic; }
    .bg-light-subtle { background-color: #fcfcfc !important; }
    .border-dark-subtle { border-color: #e5e5e5 !important; }
    
    /* Status Dot */
    .status-dot { height: 7px; width: 7px; border-radius: 50%; display: inline-block; margin-right: 4px; }
    .status-dot.pending { background: #f59e0b; }
    .status-dot.processing { background: #3b82f6; }
    .status-dot.completed { background: #10b981; }
    .status-dot.canceled { background: #ef4444; }

    .grayscale-hover { filter: grayscale(100%); transition: 0.3s; }
    .grayscale-hover:hover { filter: grayscale(0%); }

    .table td, .table th { border-bottom: 1px solid #f1f1f1; }
</style>
@endsection