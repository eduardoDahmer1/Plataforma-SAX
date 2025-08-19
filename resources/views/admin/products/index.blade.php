@extends('layout.admin')

@section('content')
<div class="container my-4">
    <!-- Contador -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <h1 class="mb-2 mb-md-0">Produtos</h1>
        <strong>Exibindo:</strong> {{ $products->count() }} de {{ $products->total() }} registros
    </div>

    <!-- Formulário de busca -->
    <form action="{{ route('admin.products.index') }}" method="GET" class="mb-4">
        <div class="input-group flex-column flex-md-row">
            <input type="text" name="search" class="form-control mb-2 mb-md-0" placeholder="Buscar por nome, SKU ou slug"
                value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>SKU</th>
                            <th>Preço</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->external_name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit me-1"></i> Editar
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                            <i class="fa fa-trash me-1"></i> Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- /table-responsive -->
        </div>
    </div>

    <!-- Paginação -->
    <div class="d-flex justify-content-center mt-3">
        {{ $products->links() }}
    </div>
</div>
@endsection
