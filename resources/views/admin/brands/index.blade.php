@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header de Marcas --}}
    <div class="dashboard-header d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Marcas</h2>
            <div class="sax-divider-dark"></div>
            <p class="text-muted x-small mt-2 mb-0">
                Mostrando <span class="text-dark fw-bold">{{ $brands->count() }}</span> de {{ $brands->total() }} marcas registradas
            </p>
        </div>
        <a href="{{ route('admin.brands.create') }}" class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
            <i class="fa fa-plus me-2"></i> Nueva Marca
        </a>
    </div>

    {{-- Barra de Busca Minimalista --}}
    <div class="sax-search-wrapper mb-4 p-3 bg-white shadow-sm rounded-4">
        <form action="{{ route('admin.brands.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0 px-3">
                    <i class="fa fa-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-0 sax-search-input" 
                    placeholder="Buscar por nombre o ID de marca..." value="{{ request('search') }}">
                <button class="btn btn-dark rounded-3 px-4" type="submit">BUSCAR</button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-sax-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Grid de Marcas --}}
    <div class="row g-4">
        @foreach($brands as $brand)
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="sax-brand-card shadow-sm h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="brand-name text-truncate text-uppercase fw-bold m-0">{{ $brand->name }}</h5>
                        <span class="badge bg-light text-dark border x-small">ID: {{ $brand->id }}</span>
                    </div>

                    {{-- Links de Acesso Rápido --}}
                    <div class="mb-4">
                        <a href="{{ route('brands.show', $brand->slug) }}" target="_blank" class="text-muted text-decoration-none x-small hover-dark">
                            <i class="fa fa-external-link-alt me-1"></i> Ver página pública
                        </a>
                    </div>

                    {{-- Ações Agrupadas --}}
                    <div class="sax-action-grid mt-auto pt-3 border-top">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('admin.brands.show', $brand) }}" class="btn btn-outline-dark btn-sax-sm w-100">
                                    <i class="fa fa-eye"></i> Admin
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-outline-dark btn-sax-sm w-100">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                            </div>
                            <div class="col-12 mt-2">
                                <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" onsubmit="return confirm('¿Eliminar marca?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-soft-danger btn-sax-sm w-100">
                                        <i class="fa fa-trash me-1"></i> Eliminar Marca
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
        {{ $brands->links() }}
    </div>
</div>
@endsection
