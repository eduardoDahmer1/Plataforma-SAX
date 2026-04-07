@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Categorias"
        description="Catálogo com <span class='text-dark fw-bold'>{{ $categories->total() }}</span> departamentos ativos">
        <x-slot:actions>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                <i class="fa fa-plus-circle me-2"></i> Nova Categoria
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Barra de Filtro Minimalista --}}
    <div class="sax-search-wrapper mb-4">
        <form action="{{ route('admin.categories.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0 px-3">
                    <i class="fa fa-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-0 sax-search-input py-2"
                    placeholder="Buscar por nombre o slug..." value="{{ request('search') }}">
                <button class="btn btn-dark rounded-3 px-4 m-1" type="submit">FILTRAR</button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-sax-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Grid de Categorias --}}
    <div class="row g-3">
        @foreach($categories as $category)
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="sax-category-card h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="category-id-badge">#{{ $category->id }}</span>
                        <a href="{{ route('categories.show', $category->slug) }}" target="_blank" class="text-muted hover-dark transition-all" title="Ver en la tienda">
                            <i class="fa fa-external-link-alt small"></i>
                        </a>
                    </div>

                    <h5 class="category-display-name mb-4">{{ $category->name ?? $category->slug }}</h5>

                    {{-- Painel de Ações Refinado --}}
                    <div class="sax-action-group mt-auto pt-3 border-top">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-action-sax w-100">
                                    <i class="fa fa-folder-open me-1"></i> Dados
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-action-sax w-100">
                                    <i class="fa fa-pen me-1"></i> Editar
                                </a>
                            </div>
                            <div class="col-12">
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('¿Desea eliminar esta categoría?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-delete-sax w-100 mt-1">
                                        <i class="fa fa-trash-alt me-1"></i> ELIMINAR CATEGORIA
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Paginação SAX --}}
    <div class="d-flex justify-content-center mt-5">
        {{ $categories->links() }}
    </div>
</x-admin.card>
@endsection
