@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header de Hierarquia Final --}}
    <div class="dashboard-header d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Categorías Hijas</h2>
            <div class="sax-divider-dark"></div>
            <p class="text-muted x-small mt-2 mb-0">
                Gestionando <span class="text-dark fw-bold">{{ $childcategories->total() }}</span> terminales de navegación
            </p>
        </div>
        <a href="{{ route('admin.childcategories.create') }}" class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
            <i class="fa fa-sitemap me-2"></i> Nueva Sub-Subcategoría
        </a>
    </div>

    {{-- Busca Refinada --}}
    <div class="sax-search-wrapper mb-4 p-2 bg-white shadow-sm rounded-4 border">
        <form action="{{ route('admin.childcategories.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0 px-3">
                    <i class="fa fa-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-0 sax-search-input py-2" 
                    placeholder="Buscar terminal por nombre..." value="{{ request('search') }}">
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

    {{-- Grid de Child Categories --}}
    <div class="row g-3">
        @foreach($childcategories as $childcategory)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="sax-child-card h-100 shadow-sm border-0 d-flex flex-column p-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge-level">NIVEL 3</span>
                            <a href="{{ route('childcategories.show', $childcategory->slug) }}" target="_blank" class="text-muted hover-dark">
                                <i class="fa fa-external-link-alt x-small"></i>
                            </a>
                        </div>
                        <h5 class="child-title text-truncate" title="{{ $childcategory->name }}">
                            {{ $childcategory->name }}
                        </h5>
                        <div class="parent-info mt-2">
                            <label class="sax-label-tiny mb-0">SUBCATEGORÍA PAI</label>
                            <p class="text-muted small text-truncate m-0">
                                <i class="fa fa-level-up-alt fa-rotate-90 me-1"></i>
                                {{ $childcategory->subcategory ? ($childcategory->subcategory->name ?: $childcategory->subcategory->slug) : 'Sin vínculo' }}
                            </p>
                        </div>
                    </div>

                    {{-- Ações Compactas --}}
                    <div class="sax-action-grid mt-auto pt-3 border-top">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('admin.childcategories.show', $childcategory) }}" class="btn btn-action-sax w-100" title="Ver Admin">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.childcategories.edit', $childcategory) }}" class="btn btn-action-sax w-100" title="Editar">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </div>
                            <div class="col-12">
                                <form action="{{ route('admin.childcategories.destroy', $childcategory) }}" method="POST" onsubmit="return confirm('¿Eliminar sub-subcategoría?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-delete-sax w-100">
                                        <i class="fa fa-trash-alt me-2"></i>ELIMINAR
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-5">
        {{ $childcategories->links() }}
    </div>
</div>
@endsection
<style>
    /* Cards Específicos para Nível 3 */
.sax-child-card {
    background: #fff;
    border-radius: 16px;
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.sax-child-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
}

.badge-level {
    font-size: 0.55rem;
    font-weight: 900;
    color: #fff;
    background: #000;
    padding: 2px 8px;
    border-radius: 4px;
    letter-spacing: 1px;
}

.child-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a1a;
    letter-spacing: -0.2px;
}

.sax-label-tiny {
    font-size: 0.55rem;
    font-weight: 800;
    color: #bbb;
    letter-spacing: 0.5px;
}

/* Botões de Ação SAX Mini */
.btn-action-sax {
    background: #f8f9fa;
    border: 1px solid #eee;
    color: #444;
    padding: 8px;
    border-radius: 8px;
    font-size: 0.8rem;
    transition: 0.2s;
}

.btn-action-sax:hover {
    background: #000;
    color: #fff;
    border-color: #000;
}

.btn-delete-sax {
    background: #fff;
    border: 1px solid #fee2e2;
    color: #dc2626;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 8px;
    border-radius: 8px;
    transition: 0.3s;
    text-transform: uppercase;
}

.btn-delete-sax:hover {
    background: #dc2626;
    color: #fff;
}

/* Reutilizando Helpers do Sistema */
.sax-divider-dark { width: 35px; height: 3px; background: #000; margin-top: 8px; }
.letter-spacing-2 { letter-spacing: 2px; }
</style>