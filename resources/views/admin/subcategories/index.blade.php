@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header Estruturado --}}
    <div class="dashboard-header d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Subcategorías</h2>
            <div class="sax-divider-dark"></div>
            <p class="text-muted x-small mt-2 mb-0">
                Estructura de <span class="text-dark fw-bold">{{ $subcategories->total() }}</span> niveles secundarios activos
            </p>
        </div>
        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
            <i class="fa fa-folder-plus me-2"></i> Nueva Subcategoría
        </a>
    </div>

    {{-- Filtro de Pesquisa --}}
    <div class="sax-search-wrapper mb-4 p-2 bg-white shadow-sm rounded-4 border">
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
                        <a href="{{ route('subcategories.show', $subcategory->id) }}" target="_blank" class="text-muted hover-dark transition-all">
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
                                    <i class="fa fa-file-alt"></i> Datos
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
</div>
@endsection
<style>
    /* Card de Subcategoria */
.sax-subcategory-card {
    background: #ffffff;
    border: 1px solid #f0f0f0;
    border-radius: 18px;
    transition: all 0.3s ease;
}

.sax-subcategory-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.06) !important;
    border-color: #000;
}

/* Tipografia e Badges */
.subcategory-tag {
    font-size: 0.6rem;
    font-weight: 800;
    color: #000;
    background: #f8f9fa;
    padding: 3px 10px;
    border-radius: 5px;
    letter-spacing: 0.5px;
}

.subcategory-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #111;
    letter-spacing: -0.3px;
}

.parent-category-badge {
    font-size: 0.7rem;
}

/* Botões Customizados (Padronizados com o Admin) */
.btn-action-sax {
    background: transparent;
    border: 1px solid #eee;
    color: #555;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 8px;
    border-radius: 10px;
    transition: 0.2s;
    text-transform: uppercase;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.btn-action-sax:hover {
    background: #111;
    color: #fff;
    border-color: #111;
}

.btn-delete-sax {
    background: #fff5f5;
    border: 1px solid #fee2e2;
    color: #e53e3e;
    font-size: 0.68rem;
    font-weight: 800;
    padding: 10px;
    border-radius: 10px;
    transition: 0.3s;
}

.btn-delete-sax:hover {
    background: #e53e3e;
    color: #fff;
}

/* Alertas SAX */
.alert-sax-success {
    background: #111;
    color: #fff;
    border-radius: 12px;
    border: none;
    font-size: 0.85rem;
}
</style>