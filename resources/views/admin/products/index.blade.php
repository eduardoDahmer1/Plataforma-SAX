@extends('layout.admin')

<style>
    .alert .close {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 1.5rem;
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
    }
</style>

@section('content')
<div class="container">

    <!-- Contador -->
    <div class="mb-3">
        <strong>Exibindo:</strong> {{ $products->count() }} de {{ $products->total() }} registros
    </div>

    <!-- Formulário de busca -->
    <form action="{{ route('admin.products.index') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nome, SKU ou slug"
                value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>SKU</th>
                <th>Preço</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->external_name }}</td>
                <td>{{ $product->sku }}</td>
                <td>R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                <td>
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline-block; margin-left: 5px;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Paginação -->
    <div class="d-flex justify-content-center">
        {{ $products->links() }}
    </div>
</div>
@endsection

<script>
document.addEventListener("DOMContentLoaded", function() {
    var closeButton = document.querySelector('.alert .close');

    if (closeButton) {
        closeButton.addEventListener('click', function() {
            var alertMessage = this.closest('.alert');
            alertMessage.style.display = 'none';
        });
    }
});
</script>
