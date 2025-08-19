@extends('layout.layout')

@section('content')
<h1 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Seu Carrinho</h1>

@if($cart->count() > 0)
@php
    $totalCarrinho = 0;
@endphp

<ul class="list-group mb-3">
    @foreach($cart as $item)
    <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start mb-2">
        <div class="mb-2 mb-md-0">
            {{-- Nome do produto --}}
            <strong><i class="fas fa-box-open me-1"></i>{{ $item->product->external_name ?? 'Produto' }}</strong><br>
            
            {{-- Slug e SKU --}}
            <small><i class="fas fa-link me-1"></i>Slug: {{ $item->product->slug ?? '-' }}</small><br>
            <small><i class="fas fa-barcode me-1"></i>SKU: {{ $item->product->sku ?? '-' }}</small>
        </div>

        <div class="d-flex align-items-center gap-3 flex-wrap">
            {{-- Preço unitário --}}
            <span><i class="fas fa-tag me-1"></i>R$ {{ number_format($item->product->price ?? 0, 2, ',', '.') }}</span>
            
            {{-- Quantidade --}}
            <span><i class="fas fa-sort-numeric-up me-1"></i>x {{ $item->quantity }}</span>

            {{-- Total do item --}}
            <span><i class="fas fa-equals me-1"></i>R$ {{ number_format(($item->product->price ?? 0) * $item->quantity, 2, ',', '.') }}</span>

            {{-- Botões --}}
            <div class="d-flex flex-column ms-2">
                {{-- + --}}
                <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm mb-1"
                        @if($item->quantity >= ($item->product->stock ?? 1)) disabled @endif>
                        <i class="fas fa-plus"></i>
                    </button>
                </form>

                {{-- - --}}
                <form action="{{ route('cart.update', $item->product_id) }}" method="POST" class="mb-1">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="quantity" value="{{ max($item->quantity - 1, 1) }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-minus"></i>
                    </button>
                </form>

                {{-- Remover --}}
                <form action="{{ route('cart.remove', $item->product_id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash me-1"></i>Excluir</button>
                </form>
            </div>
        </div>
    </li>

    @php
        $totalCarrinho += ($item->product->price ?? 0) * $item->quantity;
    @endphp
    @endforeach
</ul>

<h4 class="mb-3"><i class="fas fa-calculator me-1"></i>Total do Carrinho: R$ {{ number_format($totalCarrinho, 2, ',', '.') }}</h4>

<div class="mt-3 d-flex gap-2 flex-wrap">
    <form action="{{ route('checkout.index') }}" method="GET">
        <button type="submit" class="btn btn-success"><i class="fas fa-credit-card me-1"></i>Finalizar Compra</button>
    </form>

    <form action="{{ route('checkout.whatsapp') }}" method="GET">
        <button type="submit" class="btn btn-success"><i class="fab fa-whatsapp me-1"></i>Finalizar via WhatsApp</button>
    </form>
</div>

@else
<p><i class="fas fa-info-circle me-1"></i>Seu carrinho está vazio.</p>
@endif
@endsection
