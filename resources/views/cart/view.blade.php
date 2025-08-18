@extends('layout.layout')

@section('content')
<h1>Seu Carrinho</h1>

@if($cart->count() > 0)
<ul class="list-group mb-3">
    @php
        $totalCarrinho = 0;
    @endphp

    @foreach($cart as $item)
    <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start">
        <div class="mb-2 mb-md-0">
            {{-- Nome do produto --}}
            <strong>{{ $item->product->external_name ?? 'Produto' }}</strong><br>
            
            {{-- Slug e SKU --}}
            <small>Slug: {{ $item->product->slug ?? '-' }}</small><br>
            <small>SKU: {{ $item->product->sku ?? '-' }}</small>
        </div>

        <div class="d-flex align-items-center gap-2">
            {{-- Preço unitário --}}
            <span>R$ {{ number_format($item->product->price ?? 0, 2, ',', '.') }}</span>
            
            {{-- Quantidade --}}
            <span>x {{ $item->quantity }}</span>

            {{-- Total do item --}}
            <span>= R$ {{ number_format(($item->product->price ?? 0) * $item->quantity, 2, ',', '.') }}</span>

            {{-- Botões para adicionar, diminuir ou remover --}}
            <div class="d-flex flex-column ms-2">
                {{-- + --}}
                <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm"
                        @if($item->quantity >= ($item->product->stock ?? 1)) disabled @endif>
                        <i class="fas fa-plus"></i>
                    </button>
                </form>

                {{-- - --}}
                <form action="{{ route('cart.update', $item->product_id) }}" method="POST" class="mt-1">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="quantity" value="{{ max($item->quantity - 1, 1) }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-minus"></i>
                    </button>
                </form>

                {{-- Remover --}}
                <form action="{{ route('cart.remove', $item->product_id) }}" method="POST" class="mt-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                </form>
            </div>
        </div>

    </li>

    @php
        $totalCarrinho += ($item->product->price ?? 0) * $item->quantity;
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
