@extends('layout.layout')

@section('content')
    <h1>Seu Carrinho</h1>

    @if(count($cart) > 0)
        <ul class="list-group mb-3">
            @foreach($cart as $productId => $item)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $item['title'] ?? 'Produto' }} - Quantidade: {{ $item['quantity'] ?? 1 }}

                    <form action="{{ route('cart.remove', $productId) }}" method="POST" onsubmit="return confirm('Quer mesmo remover esse item?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                </li>
            @endforeach
        </ul>

        <a href="{{ route('checkout') }}" class="btn btn-success">Finalizar Compra</a>

    @else
        <p>Seu carrinho está vazio.</p>
    @endif
@endsection
