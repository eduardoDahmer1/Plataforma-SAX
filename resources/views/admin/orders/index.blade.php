@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.pedidos_titulo') }}"
        description="{{ __('messages.transacciones_registradas', ['total' => $orders->total()]) }}">
        <x-slot:actions>
            <button class="btn btn-sm btn-outline-dark border-0 rounded-0 text-uppercase fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fa fa-sliders-h me-2"></i> {{ __('messages.filtros') }}
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filtros em Colapso --}}
    <div class="collapse {{ request()->anyFilled(['payment_method', 'status', 'user_name']) ? 'show' : '' }} mb-5" id="filterCollapse">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 border-bottom pb-4">
            <div class="col-md-2">
                <select name="payment_method" class="form-select border-0 bg-light-subtle small rounded-0">
                    <option value="">{{ __('messages.metodo_pago_placeholder') }}</option>
                    <option value="bancard" {{ request('payment_method') == 'bancard' ? 'selected' : '' }}>Bancard</option>
                    <option value="deposito" {{ request('payment_method') == 'deposito' ? 'selected' : '' }}>Depósito</option>
                    <option value="whatsapp" {{ request('payment_method') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select border-0 bg-light-subtle small rounded-0">
                    <option value="">{{ __('messages.estado_placeholder') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.status_pending') }}</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>{{ __('messages.status_processing') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('messages.status_completed') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="user_name" class="form-control border-0 bg-light-subtle small rounded-0" 
                       placeholder="{{ __('messages.nombre_cliente_placeholder') }}" value="{{ request('user_name') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm px-4 rounded-0">{{ __('messages.aplicar') }}</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm px-4 rounded-0 border">{{ __('messages.limpiar') }}</a>
            </div>
        </form>
    </div>

    {{-- Tabela Estilo "Lista Limpa" --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle border-top">
            <thead class="bg-white">
                <tr class="text-uppercase x-small tracking-wider text-secondary">
                    <th class="py-3 border-0 fw-bold" style="width: 80px;">{{ __('messages.col_id') }}</th>
                    <th class="py-3 border-0 fw-bold">{{ __('messages.col_cliente') }}</th>
                    <th class="py-3 border-0 fw-bold">{{ __('messages.col_data') }}</th>
                    <th class="py-3 border-0 fw-bold">{{ __('messages.col_estado') }}</th>
                    <th class="py-3 border-0 fw-bold">{{ __('messages.col_metodo') }}</th>
                    <th class="py-3 border-0 fw-bold text-end">{{ __('messages.col_total') }}</th>
                    <th class="py-3 border-0 fw-bold text-end">{{ __('messages.col_acciones') }}</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($orders as $order)
                @php
                    $total = $order->items->sum(fn($item) => $item->price * $item->quantity);
                @endphp
                <tr class="border-bottom clickable-row">
                    <td class="py-4 text-dark fw-medium">#{{ $order->id }}</td>
                    <td class="py-4">
                        <span class="d-block fw-bold text-dark">{{ $order->user->name ?? __('messages.anonimo') }}</span>
                        <span class="x-small text-muted text-lowercase">{{ $order->user->email ?? '' }}</span>
                    </td>
                    <td class="py-4 text-secondary small">
                        {{ $order->created_at->format('d/m/Y') }}
                    </td>
                    <td class="py-4">
                        <span class="status-dot {{ $order->status }}"></span>
                        <span class="x-small text-uppercase fw-bold text-secondary">
                            {{ __('messages.status_' . $order->status) }}
                        </span>
                    </td>
                    <td class="py-4 text-secondary small">
                        {{ ucfirst($order->payment_method) }}
                    </td>
                    <td class="py-4 text-end fw-bold text-dark">
                        {{ number_format($total, 0, '.', '.') }} <span class="x-small fw-normal">Gs.</span>
                    </td>
                    <td class="py-4 text-end">
                        <div class="dropdown">
                            <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown">
                                <i class="fa fa-ellipsis-h"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border shadow-sm rounded-0">
                                <li><a class="dropdown-item small" href="{{ route('admin.orders.show', $order->id) }}">{{ __('messages.ver_detalles') }}</a></li>
                                <li>
                                    <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.eliminar_btn') }}?');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item small text-danger">{{ __('messages.eliminar_registro') }}</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted small">{{ __('messages.no_hay_pedidos') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="x-small text-muted text-uppercase tracking-wider">
            {{ __('messages.pagina_info', ['current' => $orders->currentPage(), 'last' => $orders->lastPage()]) }}
        </div>
        {{ $orders->links('pagination::bootstrap-4') }}
    </div>
</x-admin.card>
@endsection