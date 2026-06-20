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
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">#{{ $order->order_number }}</span>
                        <span class="badge bg-secondary">{{ $order->status }}</span>
                    </div>
                    <p class="mb-1 fw-bold">{{ $order->user->name ?? 'Anônimo' }}</p>
                    <p class="small text-muted mb-2">{{ $order->user->email ?? '' }}</p>
                    <div class="small border-top pt-2">
                        <div class="d-flex justify-content-between">
                            <span>Data:</span> <span>{{ $order->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Método:</span> <span>{{ ucfirst($order->payment_method) }}</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold mt-2">
                            <span>Total:</span> <span>{{ currency_format($order->total) }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 p-3">
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-dark w-100 rounded-0">Ver Detalhes</a>
                    <form class="btn btn-dark w-100 rounded-0" action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.eliminar_btn') }}?');">
                        @csrf @method('DELETE')
                        <button class="dropdown-item small text-danger">{{ __('messages.eliminar_registro') }}</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">Nenhum pedido encontrado.</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $orders->appends(request()->query())->links() }}
    </div>
</x-admin.card>

<script>
function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = window.location.pathname + '?' + params.toString();
}
</script>
@endsection