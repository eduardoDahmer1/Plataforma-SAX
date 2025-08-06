@extends('layout.layout')

@section('content')
<h1>Seu Carrinho</h1>

@if(count($cart) > 0)
<ul class="list-group mb-3">
    @php
        $totalCarrinho = 0; // Inicializa a variável para somar o total do carrinho
    @endphp
    @foreach($cart as $productId => $item)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        {{-- Mostra o nome do produto (name ou external_name) --}}
        <strong>{{ $item['title'] ?? 'Produto' }}</strong> 

        {{-- Exibe a quantidade --}}
        <span class="mx-2">- Quantidade: {{ $item['quantity'] ?? 1 }}</span>

        {{-- Exibe o valor total do item (Preço unitário * Quantidade) --}}
        <span>- <small>R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</small></span>

        {{-- Formulário para remover o produto do carrinho --}}
        <form action="{{ route('cart.remove', $productId) }}" method="POST"
            onsubmit="return confirm('Quer mesmo remover esse item?');" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
        </form>

        {{-- Botões para adicionar ou diminuir quantidade --}}
        <div class="d-inline-block">
            <form action="{{ route('cart.update', $productId) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-plus"></i> <!-- Ícone de adicionar -->
                </button>
            </form>
            
            {{-- Verifica se a quantidade é maior que 1, para exibir o botão de diminuir --}}
            @if ($item['quantity'] > 1)
            <form action="{{ route('cart.update', $productId) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-minus"></i> <!-- Ícone de diminuir -->
                </button>
            </form>
            @endif
        </div>
        
    </li>
    @php
        $totalCarrinho += $item['price'] * $item['quantity']; // Soma o total de cada item ao total do carrinho
    @endphp
    @endforeach
</ul>

{{-- Mostra o valor total de todos os itens no carrinho --}}
<h4>Total do Carrinho: R$ {{ number_format($totalCarrinho, 2, ',', '.') }}</h4>

{{-- Botão para finalizar a compra --}}
<form action="{{ route('checkout.index') }}" method="GET">
    <button type="submit" class="btn btn-success">Finalizar Compra</button>
</form>

@else
<p>Seu carrinho está vazio.</p>
@endif
@endsection
