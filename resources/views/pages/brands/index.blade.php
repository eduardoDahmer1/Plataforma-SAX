@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Marcas</h2>
        <!-- <a href="{{ route('admin.brands.create') }}" class="btn btn-secondary">Nova Marca</a> -->
    </div>
    
    <!-- Total de marcas exibidas -->
    <p class="text-muted">
        Exibindo {{ $brands->count() }} de {{ $brands->total() }} marca(s).
    </p>
    <!-- Formulário de busca -->
    <form action="{{ route('admin.brands.index') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nome ou ID"
                value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @foreach($brands as $brand)
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm p-3">
                    <h5>{{ $brand->name }}</h5>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.brands.show', $brand) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" onsubmit="return confirm('Tem certeza?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Excluir</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $brands->links() }}
    </div>
</div>
@endsection
