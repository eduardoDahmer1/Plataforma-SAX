@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header de Navegação --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Detalle de Subcategoría</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">Estructura jerárquica del catálogo</span>
        </div>
        <a href="{{ route('admin.subcategories.index') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
        </a>
    </div>

    <div class="row g-4">
        {{-- Card de Informações Técnicas --}}
        <div class="col-lg-5">
            <div class="sax-premium-card h-100 shadow-sm border-0">
                <div class="card-sax-header border-bottom p-4">
                    <h6 class="m-0 fw-bold letter-spacing-1 text-uppercase small text-muted">Especificaciones</h6>
                </div>
                <div class="card-sax-body p-4">
                    <div class="info-group-sax mb-4">
                        <label class="sax-label">ID DEL SISTEMA</label>
                        <div class="value-box bg-light">#{{ $subcategory->id }}</div>
                    </div>

                    <div class="info-group-sax mb-4">
                        <label class="sax-label">NOMBRE DE SUBCATEGORÍA</label>
                        <div class="value-box fw-bold text-dark fs-5 border-start border-dark border-3 rounded-0 ps-3">
                            {{ $subcategory->name ?? 'Sin Nombre' }}
                        </div>
                    </div>

                    <div class="info-group-sax">
                        <label class="sax-label">CATEGORÍA PRINCIPAL (PADRE)</label>
                        <div class="value-box bg-soft-dark border">
                            <i class="fas fa-folder me-2 text-muted"></i>
                            {{ $subcategory->category->name ?? $subcategory->category->slug ?? 'Sin Categoría' }}
                        </div>
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="btn btn-dark w-100 rounded-pill fw-bold x-small py-3 transition-all">
                            <i class="fas fa-edit me-2"></i> ACTUALIZAR INFORMACIÓN
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card de Galeria de Mídia --}}
        <div class="col-lg-7">
            <div class="sax-premium-card h-100 shadow-sm border-0">
                <div class="card-sax-header border-bottom p-4">
                    <h6 class="m-0 fw-bold letter-spacing-1 text-uppercase small text-muted">Recursos Visuales</h6>
                </div>
                
                <div class="card-sax-body p-4">
                    <div class="row g-4">
                        {{-- Foto Miniatura --}}
                        <div class="col-md-5">
                            <label class="sax-label d-block mb-3 text-center">FOTO MINIATURA</label>
                            <div class="media-preview-sax square-ratio shadow-sm">
                                @if ($subcategory->photo)
                                    <img src="{{ asset('storage/' . $subcategory->photo) }}" class="img-fluid">
                                    <div class="media-overlay"><i class="fas fa-expand"></i></div>
                                @else
                                    <div class="empty-media-box">
                                        <i class="fas fa-camera fa-2x mb-2 opacity-25"></i>
                                        <span>Sin Foto</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Banner --}}
                        <div class="col-md-7">
                            <label class="sax-label d-block mb-3 text-center">BANNER DE CABECERA</label>
                            <div class="media-preview-sax banner-ratio shadow-sm">
                                @if ($subcategory->banner)
                                    <img src="{{ asset('storage/' . $subcategory->banner) }}" class="img-fluid">
                                    <div class="media-overlay"><i class="fas fa-expand"></i></div>
                                @else
                                    <div class="empty-media-box">
                                        <i class="fas fa-images fa-2x mb-2 opacity-25"></i>
                                        <span>Sin Banner</span>
                                    </div>
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
