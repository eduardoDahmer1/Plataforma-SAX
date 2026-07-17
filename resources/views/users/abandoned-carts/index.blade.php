@extends('layout.dashboard')

@section('content')
<div class="sax-dashboard-wrapper">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="sax-title mb-2">Carrinhos abandonados</h1>
            <p class="sax-subtitle mb-0">Consulte ou recupere produtos que você removeu da sacola.</p>
        </div>
        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-dark btn-sm">Voltar ao painel</a>
    </div>

    <div class="d-flex flex-column gap-3">
        @forelse($carts as $cart)
            <div class="border rounded-3 bg-white p-4 shadow-sm">
                <div class="row align-items-center g-3">
                    <div class="col-md">
                        <small class="text-muted text-uppercase fw-bold">Carrinho #{{ $cart->id }}</small>
                        <h5 class="fw-bold mb-1 mt-1">{{ $cart->items_count }} {{ $cart->items_count === 1 ? 'item' : 'itens' }}</h5>
                        <span class="small text-muted">Abandonado em {{ $cart->abandoned_at->format('d/m/Y \à\s H:i') }}</span>
                    </div>
                    <div class="col-md-auto"><strong>{{ $cart->currency_sign }} {{ number_format($cart->total * $cart->currency_value, 2, '.', ',') }}</strong></div>
                    <div class="col-md-auto"><span class="badge {{ $cart->status === 'restored' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $cart->status === 'restored' ? 'Restaurado' : 'Abandonado' }}</span></div>
                    <div class="col-md-auto"><a href="{{ route('user.abandoned-carts.show', $cart) }}" class="btn btn-dark btn-sm px-4">Ver carrinho</a></div>
                </div>
            </div>
        @empty
            <div class="empty-state"><i class="fas fa-shopping-bag fa-2x mb-3 opacity-50"></i><p class="mb-0">Você ainda não possui carrinhos abandonados.</p></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $carts->links() }}</div>
</div>
@endsection
