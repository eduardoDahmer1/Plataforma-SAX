@extends('layout.admin')

@push('styles')
<link href="{{ asset('css/admin-commerce.css') }}?v={{ file_exists(public_path('css/admin-commerce.css')) ? filemtime(public_path('css/admin-commerce.css')) : time() }}" rel="stylesheet">
@endpush

@section('content')
<div class="commerce-admin commerce-abandoned-index">
<x-admin.card>
    <x-admin.page-header title="Carrinhos abandonados" description="Acompanhe os carrinhos descartados pelos clientes." />
    <div class="commerce-abandoned-summary">
        <span class="commerce-abandoned-summary__icon"><i class="fa-solid fa-cart-arrow-down" aria-hidden="true"></i></span>
        <div><strong>{{ $carts->total() }}</strong><span>carrinhos encontrados</span></div>
        <p>Priorize clientes que ainda precisam de ajuda para concluir a compra.</p>
    </div>
    <form method="GET" class="commerce-filter-panel commerce-filter-panel--inline row g-2 mb-4">
        <div class="col-md-6"><label>Cliente</label><div class="commerce-input-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar cliente por nome ou e-mail"></div></div>
        <div class="col-md-3"><label>Status</label><select name="status" class="form-select"><option value="">Todos os status</option><option value="abandoned" @selected(request('status')==='abandoned')>Abandonados</option><option value="restored" @selected(request('status')==='restored')>Restaurados</option></select></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-dark w-100"><i class="fa-solid fa-filter me-2" aria-hidden="true"></i>Filtrar</button></div>
    </form>
    <div class="commerce-table-wrap table-responsive">
        <table class="table commerce-table align-middle">
            <thead><tr><th>#</th><th>Cliente</th><th>Data</th><th>Itens</th><th>Total</th><th>Status</th><th class="text-end">Ação</th></tr></thead>
            <tbody>
            @forelse($carts as $cart)
                <tr><td><strong>#{{ $cart->id }}</strong></td><td><strong>{{ $cart->user->name ?? 'Cliente removido' }}</strong><small class="d-block text-muted">{{ $cart->user->email ?? '' }}</small></td><td>{{ $cart->abandoned_at->format('d/m/Y H:i') }}</td><td>{{ $cart->items_count }}</td><td><strong>{{ $cart->currency_sign }} {{ number_format($cart->total * $cart->currency_value, 2, '.', ',') }}</strong></td><td><span class="commerce-status {{ $cart->status === 'restored' ? 'is-success' : 'is-warning' }}"><i class="fa-solid fa-circle" aria-hidden="true"></i>{{ $cart->status === 'restored' ? 'Restaurado' : 'Abandonado' }}</span></td><td class="text-end"><a href="{{ route('admin.abandoned-carts.show', $cart) }}" class="btn btn-sm btn-dark"><i class="fa-solid fa-arrow-right me-2" aria-hidden="true"></i>Visualizar</a></td></tr>
            @empty<tr><td colspan="7" class="text-center text-muted py-5">Nenhum carrinho encontrado.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    {{ $carts->links() }}
</x-admin.card>
</div>
@endsection
