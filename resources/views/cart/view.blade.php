@extends('layout.layout')

@section('content')
<h1>Seu Carrinho</h1>

@if(count($cart) > 0)
<ul class="list-group mb-3">
    @php
        $totalCarrinho = 0;
    @endphp

    @foreach($cart as $productId => $item)
    <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start">
        <div class="mb-2 mb-md-0">
            {{-- Nome do produto --}}
            <strong>{{ $item['name'] ?? $item['external_name'] ?? 'Produto' }}</strong><br>
            
            {{-- Slug e SKU --}}
            <small>Slug: {{ $item['slug'] ?? '-' }}</small><br>
            <small>SKU: {{ $item['sku'] ?? '-' }}</small>
        </div>

        <div class="d-flex align-items-center gap-2">
            {{-- Preço unitário --}}
            <span>R$ {{ number_format($item['price'], 2, ',', '.') }}</span>
            
            {{-- Quantidade --}}
            <span>x {{ $item['quantity'] ?? 1 }}</span>

            {{-- Total do item --}}
            <span>= R$ {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2, ',', '.') }}</span>

            {{-- Botões para adicionar ou diminuir quantidade --}}
            <div class="d-flex flex-column ms-2">
                {{-- + --}}
                <form action="{{ route('cart.update', $productId) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="quantity" value="{{ ($item['quantity'] ?? 1) + 1 }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm"
                        @if(($item['quantity'] ?? 1) >= ($item['stock'] ?? 1)) disabled @endif>
                        <i class="fas fa-plus"></i>
                    </button>
                </form>

                {{-- - --}}
                <form action="{{ route('cart.update', $productId) }}" method="POST" class="mt-1">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="quantity" value="{{ max(($item['quantity'] ?? 1) - 1, 1) }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-minus"></i>
                    </button>
                </form>

                {{-- Remover --}}
                <form action="{{ route('cart.remove', $productId) }}" method="POST" class="mt-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                </form>
            </div>
        </div>

    </li>

    @php
        $totalCarrinho += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
    @endphp
    @endforeach
</ul>

<h4>Total do Carrinho: R$ {{ number_format($totalCarrinho, 2, ',', '.') }}</h4>

<div class="mt-3 d-flex gap-2">
    <form action="{{ route('checkout.index') }}" method="GET">
        <button type="submit" class="btn btn-success">Finalizar Compra</button>
    </form>

    <form action="{{ route('checkout.whatsapp') }}" method="GET">
        <button type="submit" class="btn btn-success">Finalizar via WhatsApp</button>
    </form>
</div>

@else
<p>Seu carrinho está vazio.</p>
@endif
@endsection
