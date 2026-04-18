@extends('layout.admin')

@section('content')
<style>
    /* Ajustes finos para Mobile */
    @media (max-width: 768px) {
        .mobile-card-item { border-bottom: 1px solid #eee; padding: 15px 0; }
        .mobile-card-item:last-child { border-bottom: none; }
        .desktop-table { display: none; }
        .header-actions { flex-direction: column; align-items: flex-start !important; gap: 15px; }
    }
    @media (min-width: 769px) {
        .mobile-view { display: none; }
    }
    .status-badge-container {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fa;
        padding: 5px 12px;
        border-radius: 50px;
    }
</style>

<x-admin.card>
    {{-- Navegação e ID --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mb-lg-5 header-actions">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-decoration-none x-small fw-bold text-uppercase text-secondary tracking-wider">
                <i class="fa fa-chevron-left me-1"></i> {{ __('messages.voltar_pedidos_btn') }}
            </a>
            <h1 class="h4 fw-bold mt-2 mb-0 text-uppercase tracking-wider">
                <span class="fw-light text-secondary">#</span>{{ $order->id }}
            </h1>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <div class="text-md-end me-md-3">
                <span class="d-block x-small text-secondary text-uppercase">{{ __('messages.data_pedido') }}</span>
                <span class="small fw-bold">{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="status-badge-container shadow-sm border">
                <span class="status-dot {{ $order->status }}"></span>
                <span class="x-small text-uppercase fw-bold text-dark">{{ __('messages.status_' . $order->status) }}</span>
            </div>
        </div>
    </div>

    <div class="row g-4 g-lg-5">
        {{-- Coluna Principal --}}
        <div class="col-lg-8 order-2 order-lg-1">
            
            {{-- Itens do Pedido --}}
            <section class="mb-5 bg-white p-3 p-lg-0 rounded">
                <h6 class="x-small fw-bold text-uppercase tracking-wider mb-4 pb-2 border-bottom">
                    <i class="fa fa-shopping-bag me-2"></i>{{ __('messages.produtos_seccao') }}
                </h6>
                
                @if ($order->items->count())
                    {{-- Tabela Desktop --}}
                    <div class="table-responsive desktop-table">
                        <table class="table align-middle">
                            <thead class="x-small text-secondary text-uppercase border-top-0">
                                <tr>
                                    <th class="border-0 ps-0">{{ __('messages.descricao_col') }}</th>
                                    <th class="border-0 text-center">{{ __('messages.quantidade_col') }}</th>
                                    <th class="border-0 text-end pe-0">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr class="border-bottom">
                                        <td class="py-3 ps-0">
                                            <span class="d-block fw-bold text-dark">{{ $item->name ?? $item->product->name ?? 'Produto' }}</span>
                                            <span class="x-small text-muted italic">SKU: {{ $item->sku ?? $item->product_id ?? '-' }}</span>
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

                    {{-- Lista Mobile --}}
                    <div class="mobile-view">
                        @foreach ($order->items as $item)
                            <div class="mobile-card-item">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-dark d-block mb-1">{{ $item->name ?? $item->product->name ?? 'Produto' }}</span>
                                    <span class="fw-bold">{{ currency_format($item->quantity * $item->price) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="x-small text-muted">Qtd: {{ $item->quantity }}</span>
                                    <span class="x-small text-muted">Ref: {{ $item->sku ?? $item->product_id ?? '-' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small">{{ __('messages.sem_items_registrados') }}</p>
                @endif
            </section>

            {{-- Logística --}}
            <section class="bg-white p-3 p-lg-0 rounded">
                <h6 class="x-small fw-bold text-uppercase tracking-wider mb-4 pb-2 border-bottom">
                    <i class="fa fa-truck me-2"></i>{{ __('messages.logistica_entrega_seccao') }}
                </h6>
                <div class="row g-4">
                    <div class="col-md-7">
                        <label class="x-small text-secondary text-uppercase fw-bold d-block mb-2">{{ __('messages.destino_label') }}</label>
                        <div class="bg-light p-3 border-start border-dark border-3 rounded-end">
                            @if ($order->shipping == 3)
                                <p class="mb-0 small fw-bold text-uppercase"><i class="fa fa-store me-2 text-primary"></i>{{ __('messages.retiro_loja_label') }}</p>
                                <span class="x-small text-muted d-block mt-1">
                                    {{ $order->store == 1 ? 'SAX Ciudad del Este' : ($order->store == 2 ? 'SAX Asunción' : 'Loja ID: ' . $order->store) }}
                                </span>
                            @else
                                <div class="small">
                                    <p class="mb-1 fw-bold text-dark">{{ $order->street ?? '-' }}, {{ $order->number ?? '' }}</p>
                                    @if($order->complement)
                                        <p class="mb-1 text-secondary small italic">{{ $order->complement }}</p>
                                    @endif
                                    <p class="mb-1">{{ $order->district ?? '-' }}</p>
                                    <p class="mb-0 text-muted text-uppercase tracking-tighter" style="font-size: 10px;">
                                        {{ $order->city ?? '-' }} / {{ $order->state ?? '-' }} — {{ $order->country ?? 'PY' }}
                                    </p>
                                    <p class="mt-2 mb-0 badge bg-dark-subtle text-dark fw-normal">CEP: {{ $order->cep ?? '-' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="x-small text-secondary text-uppercase fw-bold d-block mb-2">{{ __('messages.observacoes_label') }}</label>
                        <div class="p-3 bg-white border border-dashed text-muted italic small rounded">
                            {{ $order->observations ?: __('messages.sem_notas_adicionais') }}
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Coluna Lateral --}}
        <div class="col-lg-4 order-1 order-lg-2">
            <div class="sticky-top" style="top: 20px;">
                
                {{-- Gestão de Status --}}
                <div class="border p-4 mb-4 bg-white shadow-sm rounded">
                    <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3">{{ __('messages.gestao_pedido_card') }}</h6>
                    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                        @csrf @method('PUT')
                        <select name="status" class="form-select rounded-0 border-dark-subtle mb-3">
                            @foreach (['pending', 'processing', 'shipped', 'completed', 'canceled'] as $statusKey)
                                <option value="{{ $statusKey }}" {{ $order->status === $statusKey ? 'selected' : '' }}>
                                    {{ __('messages.status_' . $statusKey) }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-dark w-100 rounded-0 text-uppercase fw-bold tracking-wider py-2">
                            {{ __('messages.actualizar_estado_btn') }}
                        </button>
                    </form>
                </div>

                {{-- Comprador --}}
                <div class="border p-4 mb-4 bg-white shadow-sm rounded">
                    <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3 pb-2 border-bottom">{{ __('messages.comprador_card') }}</h6>
                    <div class="mb-3">
                        <span class="d-block fw-bold text-dark">{{ $order->name }} {{ $order->surname }}</span>
                        <a href="mailto:{{ $order->email }}" class="d-block small text-primary text-decoration-none mt-1">
                            <i class="fa fa-envelope me-1"></i>{{ $order->email }}
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $order->phone) }}" target="_blank" class="btn btn-outline-success btn-sm w-100 mt-3 rounded-0 fw-bold">
                            <i class="fab fa-whatsapp me-2"></i>Falar no WhatsApp
                        </a>
                    </div>
                    <div class="x-small border-top pt-3 mt-2 text-secondary">
                        <p class="mb-1">Documento: <span class="text-dark fw-bold">{{ $order->document ?? '-' }}</span></p>
                    </div>
                </div>

                {{-- Financeiro --}}
                <div class="border p-4 bg-dark text-white rounded shadow">
                    <h6 class="x-small fw-bold text-uppercase tracking-wider mb-4 border-bottom border-secondary pb-2">Pagamento</h6>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="x-small text-secondary text-uppercase">Método</span>
                        <span class="x-small fw-bold px-2 py-1 bg-secondary rounded">{{ strtoupper($order->payment_method) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 mt-3">
                        <span class="x-small text-secondary text-uppercase">Subtotal</span>
                        @php $subtotal = $order->items->sum(fn($item)=>$item->price*$item->quantity); @endphp
                        <span class="small fw-bold">{{ currency_format($subtotal) }}</span>
                    </div>

                    @if($order->discount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="x-small text-danger text-uppercase">Cupom</span>
                        <span class="small fw-bold text-danger">- {{ currency_format($order->discount) }}</span>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top border-secondary">
                        <span class="small text-uppercase fw-bold text-secondary">Total Final</span>
                        <span class="h4 mb-0 fw-bold text-white">
                            {{ currency_format($order->total) }}
                        </span>
                    </div>

                    @if ($order->deposit_receipt)
                        <div class="pt-4 mt-4 border-top border-secondary">
                            <label class="x-small fw-bold text-uppercase d-block mb-3 text-secondary text-center">Comprovante</label>
                            <a href="{{ asset('storage/deposits/' . $order->deposit_receipt) }}" target="_blank" class="d-block border border-secondary p-1 rounded bg-white">
                                <img src="{{ asset('storage/deposits/' . $order->deposit_receipt) }}" class="img-fluid d-block mx-auto" style="max-height: 150px;">
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin.card>
@endsection