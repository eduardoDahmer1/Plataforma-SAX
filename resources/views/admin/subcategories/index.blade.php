@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Subcategorias</h2>
        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary">Nova Subcategoria</a>
    </div>

    <p class="text-muted">
        Exibindo {{ $subcategories->count() }} de {{ $subcategories->total() }} subcategoria(s).
    </p>

    <form action="{{ route('admin.subcategories.index') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nome"
                value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @foreach($subcategories as $subcategory)
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm p-3">
                <h5>{{ $subcategory->name }}</h5>
                <p class="text-muted">
                    Categoria Pai:
                    {{ $subcategory->category ? ($subcategory->category->name ?: $subcategory->category->slug) : 'Sem Categoria' }}
                </p>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.subcategories.show', $subcategory) }}" class="btn btn-info btn-sm">Ver</a>
                    <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('admin.subcategories.destroy', $subcategory) }}" method="POST"
                        onsubmit="return confirm('Tem certeza?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $subcategories->links() }}
    </div>
</div>
@endsection
