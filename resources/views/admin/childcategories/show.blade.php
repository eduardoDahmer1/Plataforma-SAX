@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header com Caminho de Navegação --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Detalle de Nivel Final</h2>
            <div class="sax-divider-dark"></div>
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb x-small mb-0 text-uppercase fw-bold letter-spacing-1">
                    <li class="breadcrumb-item text-muted">{{ $childcategory->subcategory->category->name ?? 'Cat' }}</li>
                    <li class="breadcrumb-item text-muted">{{ $childcategory->subcategory->name ?? 'Sub' }}</li>
                    <li class="breadcrumb-item text-dark active">{{ $childcategory->name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.childcategories.index') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
        </a>
    </div>

    <div class="row g-4">
        {{-- Coluna de Herança e Dados --}}
        <div class="col-lg-5">
            <div class="sax-premium-card h-100 shadow-sm border-0">
                <div class="card-sax-header border-bottom p-4 bg-light rounded-top-4">
                    <h6 class="m-0 fw-bold letter-spacing-1 text-muted x-small">MAPA DE JERARQUÍA</h6>
                </div>
                <div class="card-sax-body p-4">
                    <div class="hierarchy-flow mb-5">
                        <div class="h-step mb-3">
                            <label class="sax-label-tiny">CATEGORÍA RAÍZ</label>
                            <div class="h-value border-start border-3 ps-3 py-1">
                                {{ $childcategory->subcategory->category->name ?? 'Sin categoría' }}
                            </div>
                        </div>
                        <div class="h-connector ms-3 mb-3"></div>
                        <div class="h-step mb-3">
                            <label class="sax-label-tiny">SUBCATEGORÍA INTERMEDIA</label>
                            <div class="h-value border-start border-3 ps-3 py-1">
                                {{ $childcategory->subcategory->name ?? 'Sin subcategoría' }}
                            </div>
                        </div>
                        <div class="h-connector ms-3 mb-3"></div>
                        <div class="h-step">
                            <label class="sax-label-tiny text-dark fw-bolder">NOMBRE TERMINAL</label>
                            <div class="h-value border-start border-3 border-dark ps-3 py-1 fw-bold fs-5">
                                {{ $childcategory->name }}
                            </div>
                        </div>
                    </div>

                    <div class="technical-details pt-4 border-top">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="sax-label-tiny">ID ÚNICO</label>
                                <div class="fw-bold">#{{ $childcategory->id }}</div>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="sax-label-tiny">SLUG / URL</label>
                                <div class="text-muted italic small">/{{ $childcategory->slug ?? 'n-a' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.childcategories.edit', $childcategory) }}" class="btn btn-dark w-100 rounded-pill fw-bold x-small py-3">
                            <i class="fas fa-edit me-2"></i> EDITAR ESTA CATEGORÍA
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Coluna de Ativos de Mídia --}}
        <div class="col-lg-7">
            <div class="sax-premium-card h-100 shadow-sm border-0">
                <div class="card-sax-header border-bottom p-4">
                    <h6 class="m-0 fw-bold letter-spacing-1 text-muted x-small">MULTIMEDIA DE SECCIÓN</h6>
                </div>
                
                <div class="card-sax-body p-4">
                    <div class="row g-4">
                        {{-- Foto --}}
                        <div class="col-md-5">
                            <label class="sax-label d-block mb-3 text-center">FOTO MINIATURA</label>
                            <div class="media-preview-sax shadow-sm">
                                @if ($childcategory->photo)
                                    <img src="{{ asset('storage/' . $childcategory->photo) }}" class="img-fluid">
                                @else
                                    <div class="empty-media"><i class="fas fa-image mb-2"></i><span>No cargado</span></div>
                                @endif
                            </div>
                        </div>

                        {{-- Banner --}}
                        <div class="col-md-7">
                            <label class="sax-label d-block mb-3 text-center">BANNER PUBLICITARIO</label>
                            <div class="media-preview-sax banner-height shadow-sm">
                                @if ($childcategory->banner)
                                    <img src="{{ asset('storage/' . $childcategory->banner) }}" class="img-fluid">
                                @else
                                    <div class="empty-media"><i class="fas fa-images mb-2"></i><span>Sin banner</span></div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light mt-5 border rounded-4">
                        <div class="d-flex gap-3 align-items-center">
                            <i class="fas fa-info-circle text-dark fs-4"></i>
                            <div class="small text-muted">
                                Las imágenes mostradas aquí representan el nivel final de navegación. Asegúrese de que el estilo visual sea coherente con la <strong>Categoría Raíz</strong> para mantener la armonía del catálogo.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<style>
    /* Fluxo de Hierarquia Visual */
.h-connector {
    width: 2px;
    height: 20px;
    background: #e9ecef;
}

.h-value {
    font-size: 0.95rem;
    color: #444;
}

.sax-label-tiny {
    font-size: 0.6rem;
    font-weight: 800;
    color: #aaa;
    letter-spacing: 0.5px;
    display: block;
}

/* Previews de Mídia */
.media-preview-sax {
    background: #fbfbfb;
    border: 1px solid #f0f0f0;
    border-radius: 15px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 180px;
}

.media-preview-sax.banner-height {
    min-height: 220px;
}

.media-preview-sax img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.empty-media {
    text-align: center;
    color: #ddd;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
}

/* Breadcrumb Styling */
.breadcrumb-item + .breadcrumb-item::before {
    content: "\f105";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-size: 0.7rem;
}

/* Base Sax Styles */
.sax-premium-card { background: #fff; border-radius: 20px; }
.sax-title { font-size: 1.5rem; font-weight: 900; }
.italic { font-style: italic; }
</style>