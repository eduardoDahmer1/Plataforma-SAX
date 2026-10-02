@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.pedidos_titulo') }}"
        description="{{ __('messages.transacciones_registradas', ['total' => $orders->total()]) }}">
    </x-admin.page-header>

    <div class="mb-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" id="filterForm" class="row g-3">
            <div class="col-md-2">
                <label class="x-small fw-bold text-uppercase">Método</label>
                <select name="payment_method" class="form-select rounded-0" onchange="applyFilters()">
                    <option value="">Todos</option>
                    <option value="bancard_v2" {{ request('payment_method') == 'bancard_v2' ? 'selected' : '' }}>Bancard V2</option>
                    <option value="rendix_pix" {{ request('payment_method') == 'rendix_pix' ? 'selected' : '' }}>Pix Rendix</option>
                    <option value="deposito" {{ request('payment_method') == 'deposito' ? 'selected' : '' }}>Depósito</option>
                    <option value="whatsapp" {{ request('payment_method') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="x-small fw-bold text-uppercase">Status Pedido</label>
                <select name="status" class="form-select rounded-0" onchange="applyFilters()">
                    <option value="">Todos</option>
                    @foreach (['pending', 'processing', 'shipped', 'completed', 'canceled'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ __('messages.status_' . $s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="x-small fw-bold text-uppercase">Status Pagamento</label>
                <select name="payment_status" class="form-select rounded-0" onchange="applyFilters()">
                    <option value="">Todos</option>
                    @foreach (['pending', 'paid', 'failed', 'refunded'] as $ps)
                        <option value="{{ $ps }}" {{ request('payment_status') == $ps ? 'selected' : '' }}>{{ __('messages.payment_status_' . $ps) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="x-small fw-bold text-uppercase">Exibir</label>
                <select name="per_page" class="form-select rounded-0" onchange="applyFilters()">
                    @foreach([20, 30, 50, 100] as $opt)
                        <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="x-small fw-bold text-uppercase">Cliente</label>
                <input type="text" name="user_name" class="form-control rounded-0" placeholder="Nome..." value="{{ request('user_name') }}" onchange="applyFilters()">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-0 w-100">Limpar</a>
            </div>
        </form>
    </div>

    <div class="row g-3">
        @forelse($orders as $order)
            @php
                $classeStatus = match ($order->status) {
                    'completed' => 'is-completed',
                    'shipped'   => 'is-shipped',
                    'processing'=> 'is-processing',
                    'canceled'  => 'is-canceled',
                    default     => 'is-pending',
                };
                $classePagamento = match ($order->payment_status) {
                    'paid'     => 'is-paid',
                    'failed'   => 'is-failed',
                    'refunded' => 'is-refunded',
                    default    => 'is-pending',
                };
                $subtotalCard = $order->items->sum(fn ($i) => $i->price * $i->quantity);
                $totalItens = $order->items->sum('quantity');
            @endphp

            <div class="col-12 col-md-6 col-lg-4">
                <article class="card admin-order-card h-100">
                    <div class="card-body">
                        <div class="admin-order-card__head">
                            <div>
                                <span class="admin-order-card__number">#{{ $order->order_number ?? $order->id }}</span>
                                <span class="x-small text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <span class="admin-order-status {{ $classeStatus }}">{{ __('messages.status_' . $order->status) }}</span>
                        </div>

                        <div class="admin-order-card__customer">
                            <span class="admin-order-card__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($order->user->name ?? $order->name ?? 'C', 0, 1)) }}</span>
                            <div>
                                <p>{{ $order->user->name ?? $order->name ?? '—' }}</p>
                                <span>{{ $order->user->email ?? $order->email }}</span>
                            </div>
                        </div>

                        <div class="admin-order-card__facts">
                            <div class="admin-order-card__row">
                                <span class="text-muted">{{ __('messages.metodo') }}</span>
                                <span>
                                    {{ ucfirst($order->payment_method) }}
                                    <i class="fa fa-circle admin-order-payment {{ $classePagamento }}"
                                       title="{{ __('messages.payment_status_' . $order->payment_status) }}"></i>
                                </span>
                            </div>

                            @if($order->payment_status === 'failed' && $order->payment_response_message)
                                <div class="alert alert-danger py-2 px-2 mt-2 mb-1 small">
                                    <strong>Motivo:</strong> {{ $order->payment_response_message }}
                                    @if($order->payment_response_code)<span class="d-block text-muted">Código Bancard: {{ $order->payment_response_code }}</span>@endif
                                </div>
                            @endif

                            <div class="admin-order-card__row">
                                <span class="text-muted">{{ __('messages.quantidade_col') }}</span>
                                <span>{{ $totalItens }}</span>
                            </div>

                            @if ($order->discount > 0)
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">{{ __('messages.desconto') }}</span>
                                    <span class="text-success">
                                        @if ($order->cupon)
                                            <span class="badge bg-light text-dark border me-1">{{ $order->cupon->codigo }}</span>
                                        @endif
                                        - {{ order_money($order, $order->discount) }}
                                    </span>
                                </div>
                            @endif

                            <div class="admin-order-card__total">
                                <span>{{ __('messages.total') }}</span>
                                {{-- Na moeda em que o cliente fechou o pedido --}}
                                <span>{{ order_money($order, $order->total) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer admin-order-card__footer">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-dark flex-grow-1 text-uppercase fw-bold">
                            {{ __('messages.ver_detalhes_btn') }}
                        </a>
                        <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST"
                              onsubmit="return confirm('{{ __('messages.eliminar_btn') }}?');" class="m-0">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-dark" title="{{ __('messages.eliminar_registro') }}" aria-label="{{ __('messages.eliminar_registro') }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">{{ __('messages.nenhum_pedido_encontrado') }}</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $orders->appends(request()->query())->links() }}
    </div>
</x-admin.card>
@endsection
