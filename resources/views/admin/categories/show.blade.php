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
<style>
    /* Card e Estrutura */
.sax-premium-card {
    background: #fff;
    border: 1px solid #f0f0f0;
    border-radius: 20px;
}

.card-sax-header h6 {
    font-size: 0.75rem;
    color: #000;
}

/* Campos de Informação */
.sax-label {
    font-size: 0.65rem;
    font-weight: 800;
    color: #999;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 8px;
}

.value-box {
    padding: 12px 18px;
    border-radius: 12px;
    font-size: 0.9rem;
    color: #444;
}

.italic { font-style: italic; }

/* Displays de Mídia */
.media-display-sax {
    position: relative;
    background: #f9f9f9;
    border: 1px dashed #ddd;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    transition: 0.3s;
    min-height: 200px;
}

.media-display-sax img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.media-display-sax .overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: 0.3s;
}

.media-display-sax:hover .overlay { opacity: 1; }

.no-media {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #ccc;
    font-size: 0.8rem;
    font-weight: bold;
    text-transform: uppercase;
}

/* Botão Voltar */
.btn-back-minimal {
    font-size: 0.7rem;
    font-weight: 700;
    color: #888;
    text-decoration: none;
    transition: color 0.3s;
}

.btn-back-minimal:hover { color: #000; }
</style>