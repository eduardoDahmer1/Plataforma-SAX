@extends('layout.dashboard')

@section('content')
<div class="sax-dashboard-wrapper">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div><h1 class="sax-title mb-2">Carrinho #{{ $abandonedCart->id }}</h1><p class="sax-subtitle mb-0">Salvo em {{ $abandonedCart->abandoned_at->format('d/m/Y \à\s H:i') }}</p></div>
        <a href="{{ route('user.abandoned-carts.index') }}" class="btn btn-outline-dark btn-sm">Voltar</a>
    </div>
    @if(session('success') || session('error'))<div class="alert {{ session('error') ? 'alert-danger' : 'alert-success' }}">{{ session('error') ?? session('success') }}</div>@endif

    <div class="border rounded-3 bg-white shadow-sm overflow-hidden">
        @foreach($abandonedCart->items as $item)
            <div class="d-flex align-items-center gap-3 p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                <img src="{{ $item->product?->photo_url ?? ($item->image ? asset('storage/uploads/'.$item->image) : asset('storage/uploads/noimage.webp')) }}" alt="" class="rounded border object-fit-cover" style="width:72px;height:72px">
                <div class="flex-grow-1"><strong class="d-block">{{ $item->product_name }}</strong><small class="text-muted">SKU: {{ $item->sku ?? '—' }} · Quantidade: {{ $item->quantity }}</small></div>
                <strong>{{ $abandonedCart->currency_sign }} {{ number_format($item->unit_price * $item->quantity * $abandonedCart->currency_value, 2, '.', ',') }}</strong>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center border rounded-3 bg-white p-4 mt-3">
        <div><small class="text-muted text-uppercase fw-bold">Total salvo</small><div class="h4 fw-bold mb-0">{{ $abandonedCart->currency_sign }} {{ number_format($abandonedCart->total * $abandonedCart->currency_value, 2, '.', ',') }}</div></div>
        @if($abandonedCart->status !== 'restored')
            <form action="{{ route('user.abandoned-carts.restore', $abandonedCart) }}" method="POST">@csrf<button class="btn btn-dark px-4"><i class="fas fa-undo me-2"></i>Restaurar na sacola</button></form>
        @else
            <span class="badge bg-success p-2">Carrinho restaurado</span>
        @endif
    </div>
</div>
@endsection
