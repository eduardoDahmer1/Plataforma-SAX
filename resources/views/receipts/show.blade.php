@extends($layout)

@push('styles')
<style>
    @media print {
        header, footer, aside, .sax-sidebar-card, .offcanvas, .sax-admin-header, #backToTop, [data-bs-target="#userMenu"] { display: none !important; }
        .col-md-9, .col-md-3 { width: 100% !important; }
        .d-print-none { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width: 56rem;">

    {{-- Header: breadcrumb + acciones en la misma fila --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 d-print-none">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                @if (auth()->user()->user_type == 1)
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">{{ __('messages.inicio') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.orders.index') }}" class="text-secondary text-decoration-none">{{ __('messages.menu_pedidos') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-secondary text-decoration-none">Pedido #{{ $order->id }}</a>
                    </li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ route('user.dashboard') }}" class="text-secondary text-decoration-none">{{ __('messages.minha_conta') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('user.orders.show', $order->id) }}" class="text-secondary text-decoration-none">Pedido #{{ $order->id }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">
                    {{ __('messages.ver_recibo') }} {{ $receipt->receipt_number }}
                </li>
            </ol>
        </nav>

        {{-- Acciones --}}
        <div class="d-flex gap-2">
            @if ($receipt->pdf_path)
                <a href="{{ route('receipts.download', $receipt) }}" class="btn btn-dark btn-sm">
                    {{ __('messages.descargar_pdf') }}
                </a>
            @endif
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                {{ __('messages.imprimir') }}
            </button>
        </div>

    </div>

    {{-- Card wrapper del recibo --}}
    <div class="bg-white rounded-3 shadow p-4 p-md-5">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-start pb-3 mb-4 border-bottom border-dark border-2 flex-wrap gap-3">
            <div>
                @if (!empty($webpImage))
                    <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="SAX" style="max-height: 3.5rem;">
                @else
                    <span class="fw-bolder fs-2 text-dark" style="letter-spacing: 0.1em;">SAX</span>
                @endif
            </div>
            <div class="text-end">
                <div class="fw-bold fs-5 text-dark">{{ $receipt->receipt_number }}</div>
                <div class="text-muted small">{{ __('messages.emitido_el') }} {{ $receipt->issued_at->format('d/m/Y') }}</div>
                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle text-uppercase mt-1">
                    {{ __('messages.pago_confirmado') }}
                </span>
            </div>
        </div>

        {{-- Info del pedido y cliente --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="bg-light rounded p-3 h-100">
                    <div class="text-uppercase text-muted small fw-bold mb-2" style="letter-spacing: 0.1em;">
                        {{ __('messages.dados_do_pedido') }}
                    </div>
                    <dl class="row mb-0 small">
                        <dt class="col-5 fw-normal text-muted">{{ __('messages.numero_pedido') }}</dt>
                        <dd class="col-7 text-end fw-semibold mb-1">{{ $order->order_number ?? '#' . $order->id }}</dd>

                        <dt class="col-5 fw-normal text-muted">{{ __('messages.col_data') }}</dt>
                        <dd class="col-7 text-end fw-semibold mb-1">{{ $order->created_at->format('d/m/Y') }}</dd>

                        <dt class="col-5 fw-normal text-muted">{{ __('messages.metodo_de_pago') }}</dt>
                        <dd class="col-7 text-end fw-semibold mb-1">
                            {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                        </dd>

                        @if ($order->shop_process_id)
                            <dt class="col-5 fw-normal text-muted">{{ __('messages.referencia') }}</dt>
                            <dd class="col-7 text-end fw-semibold mb-1">{{ $order->shop_process_id }}</dd>
                        @endif

                        @if ($order->shipping == 3)
                            <dt class="col-5 fw-normal text-muted">{{ __('messages.passo_metodo_entrega') }}</dt>
                            <dd class="col-7 text-end fw-semibold mb-1">{{ __('messages.retiro_en_tienda') }}</dd>
                        @elseif ($order->street)
                            <dt class="col-5 fw-normal text-muted">{{ __('messages.direccion') }}</dt>
                            <dd class="col-7 text-end fw-semibold mb-1">
                                {{ $order->street }}{{ $order->number ? ', ' . $order->number : '' }}
                                @if ($order->district) — {{ $order->district }} @endif
                            </dd>
                            <dt class="col-5 fw-normal text-muted">{{ __('messages.ciudad') }}</dt>
                            <dd class="col-7 text-end fw-semibold mb-1">
                                {{ $order->city }}{{ $order->country ? ', ' . $order->country : '' }}
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="col-md-6">
                <div class="bg-light rounded p-3 h-100">
                    <div class="text-uppercase text-muted small fw-bold mb-2" style="letter-spacing: 0.1em;">
                        {{ __('messages.dados_do_cliente') }}
                    </div>
                    <dl class="row mb-0 small">
                        <dt class="col-5 fw-normal text-muted">{{ __('messages.nome_label') }}</dt>
                        <dd class="col-7 text-end fw-semibold mb-1">{{ $order->name }}</dd>

                        @if ($order->document)
                            <dt class="col-5 fw-normal text-muted">{{ __('messages.label_documento') }}</dt>
                            <dd class="col-7 text-end fw-semibold mb-1">{{ $order->document }}</dd>
                        @endif

                        <dt class="col-5 fw-normal text-muted">{{ __('messages.email_label') }}</dt>
                        <dd class="col-7 text-end fw-semibold mb-1 text-break">{{ $order->email }}</dd>

                        @if ($order->phone)
                            <dt class="col-5 fw-normal text-muted">{{ __('messages.phone_label') }}</dt>
                            <dd class="col-7 text-end fw-semibold mb-1">{{ $order->phone }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        {{-- Productos --}}
        <div class="text-uppercase text-muted small fw-bold mb-2" style="letter-spacing: 0.1em;">
            {{ __('messages.produtos_seccao') }}
        </div>
        <div class="table-responsive mb-3">
            <table class="table align-middle small mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 4rem;"></th>
                        <th>{{ __('messages.produto_col') }}</th>
                        <th>SKU</th>
                        <th class="text-end">{{ __('messages.precio_unitario') }}</th>
                        <th class="text-end">{{ __('messages.cant_abrev') }}</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>
                                @if ($item->product && $item->product->photo_url)
                                    <img src="{{ $item->product->photo_url }}"
                                         alt="{{ $item->external_name ?? $item->name }}"
                                         class="rounded border"
                                         style="width: 3.5rem; height: 3.5rem; object-fit: cover;">
                                @else
                                    <div class="rounded border bg-light" style="width: 3.5rem; height: 3.5rem;"></div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $item->external_name ?? $item->name }}</td>
                            <td class="text-muted">{{ $item->sku ?? '-' }}</td>
                            <td class="text-end">{{ currency_format($item->price) }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">{{ currency_format($item->price * $item->quantity) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totales --}}
        <div class="border-top pt-3 mb-4">
            @if ($order->discount > 0)
                <div class="d-flex justify-content-end gap-5 small text-muted px-2 py-1">
                    <span>{{ __('messages.descuento') }}</span>
                    <span>- {{ currency_format($order->discount) }}</span>
                </div>
            @endif
            @if ($order->shipping_cost > 0)
                <div class="d-flex justify-content-end gap-5 small text-muted px-2 py-1">
                    <span>{{ __('messages.envio') }}</span>
                    <span>{{ currency_format($order->shipping_cost) }}</span>
                </div>
            @endif
            <div class="d-flex justify-content-end gap-5 bg-dark text-white fw-bolder rounded p-3 mt-2">
                <span>Total</span>
                <span>{{ currency_format($order->total) }}</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-muted small border-top pt-3">
            {{ __('messages.comprobante_oficial') }}<br>
            {{ $receipt->receipt_number }} &mdash; {{ $receipt->issued_at->format('d/m/Y H:i') }}
        </div>

    </div>
</div>
@endsection
