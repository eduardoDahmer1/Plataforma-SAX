@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Subcategorias"
        description="Estrutura de <span class='text-dark fw-bold'>{{ $subcategories->total() }}</span> níveis secundários ativos">
        <x-slot:actions>
            <a href="{{ route('admin.subcategories.create') }}" class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                <i class="fa fa-folder-plus me-2"></i> Nova Subcategoria
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filtro de Pesquisa --}}
    <div class="sax-search-wrapper mb-4">
        <form action="{{ route('admin.subcategories.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0 px-3">
                    <i class="fa fa-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-0 sax-search-input py-2"
                    placeholder="Buscar por nombre de subcategoría..." value="{{ request('search') }}">
                <button class="btn btn-dark rounded-3 px-4 m-1" type="submit">BUSCAR</button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-sax-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Grid de Subcategorias --}}
    <div class="row g-3">
        @foreach($subcategories as $subcategory)
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="sax-subcategory-card h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="subcategory-tag">Subnivel #{{ $subcategory->id }}</span>
                        <a href="{{ route('subcategories.show', $subcategory->slug) }}" target="_blank" class="text-muted hover-dark transition-all">
                            <i class="fa fa-external-link-alt small"></i>
                        </a>
                    </div>

                    <h5 class="subcategory-title mb-1">{{ $subcategory->name }}</h5>

                    {{-- Badge da Categoria Pai --}}
                    <div class="parent-category-badge mb-4">
                        <i class="fa fa-level-up-alt fa-rotate-90 me-1 opacity-50"></i>
                        <span class="text-muted">Perteneciente a:</span>
                        <strong class="text-dark">
                            {{ $subcategory->category ? ($subcategory->category->name ?: $subcategory->category->slug) : 'Sin categoría' }}
                        </strong>
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="sax-action-group mt-auto pt-3 border-top">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('admin.subcategories.show', $subcategory) }}" class="btn btn-action-sax w-100">
                                    <i class="fa fa-file-alt"></i> Dados
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="btn btn-action-sax w-100">
                                    <i class="fa fa-pen"></i> Editar
                                </a>
                            </div>
                            <div class="col-12">
                                <form action="{{ route('admin.subcategories.destroy', $subcategory) }}" method="POST" onsubmit="return confirm('¿Eliminar subcategoría?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-delete-sax w-100 mt-1">
                                        <i class="fa fa-trash-alt me-1"></i> ELIMINAR REGISTRO
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

    <div class="d-flex justify-content-center mt-5">
        {{ $subcategories->links() }}
    </div>
</x-admin.card>
@endsection
