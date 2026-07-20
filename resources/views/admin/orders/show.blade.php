@extends('layout.admin')

@section('content')

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
            
        <section class="mb-5 bg-white p-3 p-lg-4 rounded shadow-sm border">
            <h6 class="fw-bold text-uppercase tracking-wider mb-4 pb-2 border-bottom">
                <i class="fa fa-shopping-bag me-2"></i>{{ __('messages.produtos_seccao') }}
            </h6>
            
            @if ($order->items->count())
                <div class="table-responsive desktop-table">
                    <table class="table align-middle">
                        <thead class="x-small text-secondary text-uppercase">
                            <tr>
                                <th class="border-0 ps-0" style="width: 80px;"></th>
                                <th class="border-0">{{ __('messages.descricao_col') }}</th>
                                <th class="border-0 text-center">{{ __('messages.quantidade_col') }}</th>
                                <th class="border-0 text-end pe-0">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                @php $product = $item->product; @endphp
                                <tr class="border-bottom">
                                    <td class="py-3 ps-0">
                                        @if($product)
                                            <img src="{{ $product->photo_url }}" alt="{{ $item->name }}" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                <i class="fa fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <span class="d-block fw-bold text-dark">{{ $item->name ?? ($product->name ?? 'Produto') }}</span>
                                        <span class="x-small text-muted">SKU: {{ $item->sku ?? ($product->sku ?? '-') }}</span>
                                        @if($product && $product->color) <span class="badge bg-secondary x-small ms-1">{{ $product->color }}</span> @endif
                                    </td>
                                    <td class="py-3 text-center text-secondary">{{ $item->quantity }}</td>
                                    <td class="py-3 text-end pe-0 fw-bold text-dark">
                                        {{ order_money($order, $item->quantity * $item->price) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mobile-view">
                    @foreach ($order->items as $item)
                        @php $product = $item->product; @endphp
                        <div class="mobile-card-item border-bottom pb-3 mb-3">
                            <div class="d-flex align-items-start gap-3">
                                @if($product)
                                    <img src="{{ $product->photo_url }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @endif
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-dark">{{ $item->name ?? ($product->name ?? 'Produto') }}</span>
                                        <span class="fw-bold">{{ order_money($order, $item->quantity * $item->price) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="x-small text-muted">Qtd: {{ $item->quantity }}</span>
                                        <span class="x-small text-muted">Ref: {{ $item->sku ?? ($product->sku ?? '-') }}</span>
                                    </div>
                                </div>
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
                @if($order->payment_status === 'failed' && $order->payment_response_message)
                    <div class="alert alert-danger mb-4">
                        <strong class="d-block mb-1"><i class="fa fa-circle-exclamation me-1"></i>Por que o pagamento falhou</strong>
                        {{ $order->payment_response_message }}
                        @if($order->payment_response_code)<small class="d-block mt-1">Retorno Bancard: {{ $order->payment_response_code }}</small>@endif
                    </div>
                @endif
                <div class="border p-4 mb-4 bg-white shadow-sm rounded">
                    <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3 pb-2 border-bottom">{{ __('messages.gestao_pedido_card') }}</h6>
                    
                    {{-- Form 1: Status do Pedido --}}
                    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="mb-4">
                        @csrf 
                        @method('PUT')
                        <div class="mb-3">
                            <label class="x-small text-secondary text-uppercase fw-bold mb-1 d-block">{{ __('messages.status_pedido_label') }}</label>
                            <select name="status" class="form-select rounded-0 border-dark-subtle mb-2">
                                @foreach (['pending', 'processing', 'shipped', 'completed', 'canceled'] as $statusKey)
                                    <option value="{{ $statusKey }}" {{ $order->status === $statusKey ? 'selected' : '' }}>
                                        {{ __('messages.status_' . $statusKey) }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-dark w-100 rounded-0 text-uppercase fw-bold tracking-wider py-2">
                                {{ __('messages.actualizar_estado_btn') }}
                            </button>
                        </div>
                    </form>

                    <div class="border-top my-3"></div>

                    {{-- Form 2: Status do Pagamento --}}
                    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                        @csrf 
                        @method('PUT')
                        <div class="mb-3">
                            <label class="x-small text-secondary text-uppercase fw-bold mb-1 d-block">{{ __('messages.status_pagamento_label') }}</label>
                            <select name="payment_status" class="form-select rounded-0 border-dark-subtle mb-2">
                                @foreach (['pending', 'paid', 'failed', 'refunded'] as $payStatus)
                                    <option value="{{ $payStatus }}" {{ $order->payment_status === $payStatus ? 'selected' : '' }}>
                                        {{ __('messages.payment_status_' . $payStatus) }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-dark w-100 rounded-0 text-uppercase fw-bold tracking-wider py-2">
                                {{ __('messages.actualizar_pagamento_btn') }}
                            </button>
                        </div>
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
                        
                        @if($order->phone)
                            @php
                                // Prefijo del telefono del perfil del usuario (55=BR, 595=PY). 595 por defecto
                                $ddi = optional($order->user)->phone_country ?: '595';
                                $whatsapp = $ddi . preg_replace('/\D/', '', $order->phone);
                            @endphp
                            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" class="btn btn-outline-success btn-sm w-100 mt-3 rounded-0 fw-bold">
                                <i class="fab fa-whatsapp me-2"></i>{{ $order->phone }}
                            </a>
                        @endif
                    </div>

                    <div class="x-small border-top pt-3 mt-2 text-secondary">
                        <p class="mb-1">Documento: <span class="text-dark fw-bold">{{ $order->document ?? '-' }}</span></p>

                        @if($order->user && $order->user->country)
                            <p class="mb-1">País: <span class="text-dark">{{ $order->user->country }}</span></p>
                        @endif

                        @if($order->user && $order->user->additional_info)
                            <div class="mt-2 p-2 bg-light border">
                                <span class="d-block fw-bold mb-1">Informações Adicionais:</span>
                                <span class="text-dark">{{ $order->user->additional_info }}</span>
                            </div>
                        @endif

                        @if($order->user && $order->user->created_at)
                            <p class="mt-2 mb-0">Cliente desde: {{ $order->user->created_at->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Recibo --}}
                @if ($order->receipt && $order->payment_status === 'paid')
                <div class="border p-4 mb-4 bg-white shadow-sm rounded">
                    <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3 pb-2 border-bottom">{{ __('messages.recibo_de_compra') }}</h6>
                    <p class="x-small text-secondary mb-3">
                        {{ $order->receipt->receipt_number }} &mdash; {{ $order->receipt->issued_at->format('d/m/Y') }}
                    </p>
                    <a href="{{ route('receipts.show', $order->receipt) }}" class="btn btn-dark btn-sm w-100 rounded-0 fw-bold text-uppercase mb-2">
                        {{ __('messages.ver_recibo') }}
                    </a>
                    @if ($order->receipt->pdf_path && \Storage::exists($order->receipt->pdf_path))
                    <a href="{{ route('receipts.download', $order->receipt) }}" class="btn btn-outline-dark btn-sm w-100 rounded-0 fw-bold text-uppercase">
                        {{ __('messages.descargar_pdf') }}
                    </a>
                    @endif
                </div>
                @endif

                {{-- Financeiro --}}
                @php
                    $subtotal = $order->items->sum(fn ($item) => $item->price * $item->quantity);
                    $descontoPedido = (float) ($order->discount ?? 0);
                    $moedaPedido = trim((string) ($order->currency_sign ?? '')) ?: 'US$';
                    $cotacaoPedido = (float) ($order->currency_value ?? 1) ?: 1;
                @endphp

                <div class="border p-4 bg-dark text-white rounded shadow">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-2">
                        <h6 class="x-small fw-bold text-uppercase tracking-wider mb-0">{{ __('messages.pagamento_titulo') }}</h6>
                        {{-- Moeda travada no fechamento do pedido: é o que o cliente viu e pagou. --}}
                        <span class="x-small fw-bold px-2 py-1 bg-light text-dark rounded" title="{{ __('messages.pedido_moeda_ajuda') }}">
                            {{ $moedaPedido }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="x-small text-secondary text-uppercase">{{ __('messages.metodo') }}</span>
                        <span class="x-small fw-bold px-2 py-1 bg-secondary rounded">{{ strtoupper($order->payment_method) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2 mt-3">
                        <span class="x-small text-secondary text-uppercase">{{ __('messages.subtotal') }}</span>
                        <span class="small fw-bold">{{ order_money($order, $subtotal) }}</span>
                    </div>

                    @if ($descontoPedido > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="x-small text-uppercase" style="color:#7fd18f;">
                                {{ __('messages.desconto') }}
                                @if ($order->cupon)
                                    <span class="badge bg-light text-dark ms-1">{{ $order->cupon->codigo }}</span>
                                @endif
                            </span>
                            <span class="small fw-bold" style="color:#7fd18f;">- {{ order_money($order, $descontoPedido) }}</span>
                        </div>

                        @if ($order->cupon)
                            <div class="x-small text-secondary mb-2 ps-1">
                                {{ $order->cupon->rotuloDesconto() }} · {{ $order->cupon->rotuloEscopo() }}
                            </div>
                        @endif
                    @endif

                    <div class="d-flex justify-content-between mb-2">
                        <span class="x-small text-secondary text-uppercase">{{ __('messages.frete') }}</span>
                        <span class="small fw-bold">{{ order_money($order, $order->shipping_cost ?? 0) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top border-secondary">
                        <span class="small text-uppercase fw-bold text-secondary">{{ __('messages.total_final') }}</span>
                        <span class="h4 mb-0 fw-bold text-white">
                            {{ order_money($order, $order->total) }}
                        </span>
                    </div>

                    {{-- Referência em USD: os valores são gravados na moeda base. --}}
                    @if ($moedaPedido !== 'US$')
                        <div class="text-end x-small text-secondary mt-1">
                            {{ __('messages.pedido_equivale_base', ['valor' => 'US$ ' . number_format($order->total, 2, ',', '.')]) }}
                            · {{ __('messages.pedido_cotacao', ['valor' => rtrim(rtrim(number_format($cotacaoPedido, 4, ',', '.'), '0'), ',')]) }}
                        </div>
                    @endif

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
