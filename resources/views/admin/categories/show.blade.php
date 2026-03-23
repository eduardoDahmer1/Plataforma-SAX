@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header de Navegação --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Ficha de la Categoría</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">Visualización detallada de recursos</span>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
        </a>
    </div>

    <div class="row g-4">
        {{-- Coluna de Informações --}}
        <div class="col-lg-5">
            <div class="sax-premium-card h-100">
                <div class="card-sax-header border-bottom p-4">
                    <h6 class="m-0 fw-bold letter-spacing-1">DATOS PRINCIPALES</h6>
                </div>
                <div class="card-sax-body p-4">
                    <div class="info-group-sax mb-4">
                        <label class="sax-label">IDENTIFICADOR ÚNICO</label>
                        <div class="value-box bg-light">#{{ $category->id }}</div>
                    </div>

                    <div class="info-group-sax mb-4">
                        <label class="sax-label">NOMBRE DE DEPARTAMENTO</label>
                        <div class="value-box fw-bold text-dark fs-5">{{ $category->name ?? 'Sin Nombre' }}</div>
                    </div>

                    <div class="info-group-sax">
                        <label class="sax-label">URL AMIGABLE (SLUG)</label>
                        <div class="value-box text-muted italic">/{{ $category->slug ?? 'sin-slug' }}</div>
                    </div>

                    <div class="mt-5 pt-3">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-dark w-100 rounded-pill fw-bold x-small py-2">
                            <i class="fas fa-edit me-2"></i> EDITAR ESTA CATEGORÍA
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Coluna de Mídia (Imagens) --}}
        <div class="col-lg-7">
            <div class="sax-premium-card h-100">
                <div class="card-sax-header border-bottom p-4 d-flex justify-content-between">
                    <h6 class="m-0 fw-bold letter-spacing-1">RECURSOS VISUALES</h6>
                </div>
                
                <div class="card-sax-body p-4">
                    <div class="row g-4">
                        {{-- Foto Miniatura --}}
                        <div class="col-md-5">
                            <label class="sax-label d-block mb-3 text-center">FOTO MINIATURA</label>
                            <div class="media-display-sax photo-square">
                                @if ($category->photo)
                                    <img src="{{ asset('storage/' . $category->photo) }}" class="img-fluid rounded">
                                    <div class="overlay"><i class="fas fa-search-plus"></i></div>
                                @else
                                    <div class="no-media"><i class="fas fa-image mb-2"></i><span>Vacío</span></div>
                                @endif
                            </div>
                        </div>

                        {{-- Banner --}}
                        <div class="col-md-7">
                            <label class="sax-label d-block mb-3 text-center">BANNER PUBLICITARIO</label>
                            <div class="media-display-sax banner-rect">
                                @if ($category->banner)
                                    <img src="{{ asset('storage/' . $category->banner) }}" class="img-fluid rounded">
                                    <div class="overlay"><i class="fas fa-search-plus"></i></div>
                                @else
                                    <div class="no-media"><i class="fas fa-images mb-2"></i><span>Sin Banner</span></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
