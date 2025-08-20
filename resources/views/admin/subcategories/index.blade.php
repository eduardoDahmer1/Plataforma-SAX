@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <h2 class="mb-2 mb-md-0">Subcategorias</h2>
        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary">Nova Subcategoria</a>
    </div>

    <p class="text-muted mb-3">
        Exibindo {{ $subcategories->count() }} de {{ $subcategories->total() }} subcategoria(s).
    </p>

    <!-- Formulário de busca -->
    <form action="{{ route('admin.subcategories.index') }}" method="GET" class="mb-4">
        <div class="d-flex flex-column flex-md-row gap-2">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nome" value="{{ request('search') }}">
            <button class="btn btn-primary flex-shrink-0" type="submit">Buscar</button>
        </div>
    </form>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="row g-3">
        @foreach($subcategories as $subcategory)
            <div class="col-12 col-sm-6 col-md-4 col-lg-4">
                <div class="card shadow-sm h-100 d-flex flex-column">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-2 text-truncate">{{ $subcategory->name }}</h5>
                        <p class="text-muted mb-3">
                            Categoria Pai: {{ $subcategory->category ? ($subcategory->category->name ?: $subcategory->category->slug) : 'Sem Categoria' }}
                        </p>
                        <div class="d-flex flex-column flex-md-row gap-2 mt-auto">
                            <a href="{{ route('admin.subcategories.show', $subcategory) }}" class="btn btn-info btn-sm flex-fill">
                                <i class="fa fa-eye me-1"></i> Ver
                            </a>
                            <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="btn btn-warning btn-sm flex-fill">
                                <i class="fa fa-edit me-1"></i> Editar
                            </a>
                            <form action="{{ route('admin.subcategories.destroy', $subcategory) }}" method="POST" onsubmit="return confirm('Tem certeza?')" class="flex-fill m-0">
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
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $subcategories->links() }}
    </div>
</div>
@endsection
