@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <h2 class="mb-2 mb-md-0">Categorias Filhas</h2>
        <a href="{{ route('admin.childcategories.create') }}" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> Nova Sub-Subcategoria
        </a>
    </div>

    <p class="text-muted mb-3">
        Exibindo {{ $childcategories->count() }} de {{ $childcategories->total() }} sub-subcategoria(s).
    </p>

    <form action="{{ route('admin.childcategories.index') }}" method="GET" class="mb-4">
        <div class="input-group flex-column flex-md-row gap-2">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nome"
                value="{{ request('search') }}">
            <button class="btn btn-primary flex-shrink-0" type="submit">
                <i class="fa fa-search me-1"></i> Buscar
            </button>
        </div>
    </form>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
    @endif

    <div class="row">
        @foreach($childcategories as $childcategory)
        <div class="col-12 col-sm-6 col-md-4 mb-3">
            <div class="card shadow-sm p-3 h-100 d-flex flex-column justify-content-between">
                <h5 class="mb-2">{{ $childcategory->name }}</h5>
                <p class="text-muted mb-3">
                    Subcategoria Pai:
                    {{ $childcategory->subcategory ? ($childcategory->subcategory->name ?: $childcategory->subcategory->slug) : 'Sem Subcategoria' }}
                </p>
                <div class="row g-2 mt-auto">
                    <div class="col-6 d-grid">
                        <a href="{{ route('admin.childcategories.show', $childcategory) }}"
                            class="btn btn-info btn-sm w-100">
                            <i class="fa fa-eye me-1"></i> Ver Admin
                        </a>
                    </div>
                    <div class="col-6 d-grid">
                        <a href="{{ route('childcategories.show', $childcategory->slug) }}" target="_blank"
                            class="btn btn-success btn-sm w-100">
                            <i class="fa fa-eye me-1"></i> Ver Pública
                        </a>
                    </div>
                    <div class="col-6 d-grid">
                        <a href="{{ route('admin.childcategories.edit', $childcategory) }}"
                            class="btn btn-warning btn-sm w-100">
                            <i class="fa fa-edit me-1"></i> Editar
                        </a>
                    </div>
                    <div class="col-6 d-grid">
                        <form action="{{ route('admin.childcategories.destroy', $childcategory) }}" method="POST"
                            onsubmit="return confirm('Tem certeza?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm w-100">
                                <i class="fa fa-trash me-1"></i> Excluir
                            </button>
                        </form>
                    </div>
                </div>
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