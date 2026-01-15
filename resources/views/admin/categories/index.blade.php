@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Cabeçalho Estilizado --}}
    <div class="dashboard-header d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Categorías</h2>
            <div class="sax-divider-dark"></div>
            <p class="text-muted x-small mt-2 mb-0">
                Catálogo con <span class="text-dark fw-bold">{{ $categories->total() }}</span> departamentos activos
            </p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
            <i class="fa fa-plus-circle me-2"></i> Nueva Categoría
        </a>
    </div>

    {{-- Barra de Filtro Minimalista --}}
    <div class="sax-search-wrapper mb-4 p-2 bg-white shadow-sm rounded-4 border">
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
                                    <i class="fa fa-folder-open me-1"></i> Datos
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
                                        <i class="fa fa-trash-alt me-1"></i> ELIMINAR CATEGORÍA
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
</div>
@endsection
<style>
    /* Card de Categoria */
.sax-category-card {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 18px;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.sax-category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.07) !important;
    border-color: #000;
}

/* ID Badge */
.category-id-badge {
    font-size: 0.65rem;
    font-weight: 800;
    color: #999;
    background: #f8f9fa;
    padding: 4px 10px;
    border-radius: 6px;
}

/* Tipografia */
.category-display-name {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1a1a1a;
    letter-spacing: -0.5px;
    text-transform: capitalize;
}

/* Botões de Ação Personalizados */
.btn-action-sax {
    background: transparent;
    border: 1px solid #eee;
    color: #444;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 10px;
    border-radius: 10px;
    transition: 0.3s;
    text-transform: uppercase;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-action-sax:hover {
    background: #000;
    border-color: #000;
    color: #fff;
}

.btn-delete-sax {
    background: #fff5f5;
    border: 1px solid #fee2e2;
    color: #dc2626;
    font-size: 0.7rem;
    font-weight: 800;
    padding: 10px;
    border-radius: 10px;
    transition: 0.3s;
}

.btn-delete-sax:hover {
    background: #dc2626;
    color: #fff;
}

/* Alerta SAX Dark */
.alert-sax-success {
    background-color: #1a1a1a;
    border: none;
    color: #fff;
    border-radius: 15px;
    font-weight: 500;
}

/* Custom Search */
.sax-search-input:focus {
    box-shadow: none;
}
</style>