@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header title="Carrinhos abandonados" description="Acompanhe os carrinhos descartados pelos clientes." />
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-6"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar cliente por nome ou e-mail"></div>
        <div class="col-md-3"><select name="status" class="form-select"><option value="">Todos os status</option><option value="abandoned" @selected(request('status')==='abandoned')>Abandonados</option><option value="restored" @selected(request('status')==='restored')>Restaurados</option></select></div>
        <div class="col-md-3"><button class="btn btn-dark w-100">Filtrar</button></div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>#</th><th>Cliente</th><th>Data</th><th>Itens</th><th>Total</th><th>Status</th><th class="text-end">Ação</th></tr></thead>
            <tbody>
            @forelse($carts as $cart)
                <tr><td>#{{ $cart->id }}</td><td><strong>{{ $cart->user->name ?? 'Cliente removido' }}</strong><small class="d-block text-muted">{{ $cart->user->email ?? '' }}</small></td><td>{{ $cart->abandoned_at->format('d/m/Y H:i') }}</td><td>{{ $cart->items_count }}</td><td>{{ $cart->currency_sign }} {{ number_format($cart->total * $cart->currency_value, 2, '.', ',') }}</td><td><span class="badge {{ $cart->status === 'restored' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $cart->status === 'restored' ? 'Restaurado' : 'Abandonado' }}</span></td><td class="text-end"><a href="{{ route('admin.abandoned-carts.show', $cart) }}" class="btn btn-sm btn-outline-dark">Visualizar</a></td></tr>
            @empty<tr><td colspan="7" class="text-center text-muted py-5">Nenhum carrinho encontrado.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    {{ $carts->links() }}
</x-admin.card>
@endsection
