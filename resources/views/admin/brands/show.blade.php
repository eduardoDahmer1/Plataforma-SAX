@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header de Navegação --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Perfil de la Marca</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">Gestión de activos de identidad</span>
        </div>
        <a href="{{ route('admin.brands.index') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
        </a>
    </div>

    <div class="row g-4">
        {{-- Coluna de Informações --}}
        <div class="col-lg-4">
            <div class="sax-premium-card h-100 shadow-sm border-0">
                <div class="card-sax-header border-bottom p-4">
                    <h6 class="m-0 fw-bold letter-spacing-1 text-muted small">INFORMACIÓN GENERAL</h6>
                </div>
                <div class="card-sax-body p-4 text-center">
                    {{-- Logo em destaque no perfil --}}
                    <div class="brand-profile-logo mb-4 mx-auto shadow-sm">
                        @if ($brand->image)
                            <img src="{{ asset('storage/' . $brand->image) }}" alt="Logo" class="img-fluid">
                        @else
                            <div class="empty-logo"><i class="fas fa-industry fa-2x"></i></div>
                        @endif
                    </div>

                    <div class="info-group-sax mb-3 text-start">
                        <label class="sax-label">ID DEL SISTEMA</label>
                        <div class="value-box bg-light x-small fw-bold">#{{ $brand->id }}</div>
                    </div>

                    <div class="info-group-sax mb-4 text-start">
                        <label class="sax-label">NOMBRE DE LA MARCA</label>
                        <div class="value-box fw-bold text-dark fs-4">{{ $brand->name ?? 'Sin Nombre' }}</div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-dark w-100 rounded-pill fw-bold x-small py-3">
                            <i class="fas fa-pen-nib me-2"></i> EDITAR IDENTIDAD
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Coluna de Banner e Preview --}}
        <div class="col-lg-8">
            <div class="sax-premium-card h-100 shadow-sm border-0">
                <div class="card-sax-header border-bottom p-4">
                    <h6 class="m-0 fw-bold letter-spacing-1 text-muted small">BANNER PUBLICITARIO (HEADLINE)</h6>
                </div>
                
                <div class="card-sax-body p-4">
                    @if ($brand->banner)
                        <div class="brand-banner-preview position-relative overflow-hidden rounded-4 shadow-sm">
                            <img src="{{ asset('storage/' . $brand->banner) }}" alt="Banner" class="img-fluid">
                            <div class="banner-badge">Official Banner</div>
                        </div>
                    @else
                        <div class="empty-banner-state py-5">
                            <i class="fas fa-images fa-3x mb-3 opacity-25"></i>
                            <p class="text-uppercase letter-spacing-1 small fw-bold">No hay banner cargado</p>
                            <span class="text-muted x-small">Se recomienda una imagen de 1920x400px</span>
                        </div>
                    @endif

                    <div class="mt-5 bg-light p-4 rounded-4 border">
                        <h6 class="sax-label mb-3"><i class="fas fa-info-circle me-1"></i> Nota de Diseño</h6>
                        <p class="text-muted small mb-0">
                            Este banner se utilizará en la cabecera de la página de la marca <strong>{{ $brand->name }}</strong>. 
                            Asegúrese de que el logo tenga suficiente contraste con el fondo para mantener la legibilidad premium.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<style>
    /* Container de Logo Circular/Quadrado Suave */
.brand-profile-logo {
    width: 140px;
    height: 140px;
    background: #fff;
    border-radius: 20%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
    border: 1px solid #f0f0f0;
}

.brand-profile-logo img {
    max-height: 100%;
    object-fit: contain;
}

.empty-logo {
    color: #e0e0e0;
}

/* Banner Styling */
.brand-banner-preview {
    background: #f8f9fa;
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-banner-preview img {
    width: 100%;
    height: auto;
    object-fit: cover;
}

.banner-badge {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.9);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.6rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #000;
}

/* Empty State */
.empty-banner-state {
    border: 2px dashed #eee;
    border-radius: 20px;
    text-align: center;
    color: #ccc;
}

/* Sax Styles Sync */
.sax-label {
    font-size: 0.6rem;
    font-weight: 800;
    color: #999;
    letter-spacing: 1px;
    margin-bottom: 8px;
    display: block;
}

.value-box {
    padding: 10px 15px;
    border-radius: 10px;
}
</style>