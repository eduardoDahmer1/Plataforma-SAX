@extends('layout.layout')

@section('content')
<h1>Seu Carrinho</h1>

@if(count($cart) > 0)
<ul class="list-group mb-3">
    @foreach($cart as $productId => $item)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        {{ $item['slug'] ?? 'Produto' }} - Quantidade: {{ $item['quantity'] ?? 1 }} - <small>R$ {{ number_format($item['price'] ?? 0, 2, ',', '.') }}</small>

        <form action="{{ route('cart.remove', $productId) }}" method="POST"
            onsubmit="return confirm('Quer mesmo remover esse item?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
        </form>
        
    </li>
    @endforeach
</ul>

@if(count($cart))
<form action="{{ route('checkout.step1') }}" method="GET">
    <button type="submit" class="btn btn-success">Finalizar Compra</button>
</form>
@endif

@else
<p>Seu carrinho está vazio.</p>
@endif
@endsection