@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Sub-Subcategorias</h2>
        <a href="{{ route('admin.childcategories.create') }}" class="btn btn-primary">Nova Sub-Subcategoria</a>
    </div>

    <p class="text-muted">
        Exibindo {{ $childcategories->count() }} de {{ $childcategories->total() }} sub-subcategoria(s).
    </p>

    <form action="{{ route('admin.childcategories.index') }}" method="GET" class="mb-4">
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
        @foreach($childcategories as $childcategory)
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm p-3">
                <h5>{{ $childcategory->name }}</h5>
                <p class="text-muted">
                    Subcategoria Pai:
                    {{ $childcategory->subcategory ? ($childcategory->subcategory->name ?: $childcategory->subcategory->slug) : 'Sem Subcategoria' }}
                </p>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.childcategories.show', $childcategory) }}" class="btn btn-info btn-sm">Ver</a>
                    <a href="{{ route('admin.childcategories.edit', $childcategory) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('admin.childcategories.destroy', $childcategory) }}" method="POST"
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
        {{ $childcategories->links() }}
    </div>
</div>
@endsection
