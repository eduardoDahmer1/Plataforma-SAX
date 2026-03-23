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
                        <li class="breadcrumb-item text-muted">{{ $categoriasfilhas->subcategory->category->name ?? 'Cat' }}
                        </li>
                        <li class="breadcrumb-item text-muted">{{ $categoriasfilhas->subcategory->name ?? 'Sub' }}</li>
                        <li class="breadcrumb-item text-dark active">{{ $categoriasfilhas->name }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.categorias-filhas.edit', $categoriasfilhas) }}"
                class="btn btn-dark w-100 rounded-pill fw-bold x-small py-3">
                <i class="fas fa-edit me-2"></i> EDITAR ESTA CATEGORÍA
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
                                    {{ $categoriasfilhas->subcategory->category->name ?? 'Sin categoría' }}
                                </div>
                            </div>
                            <div class="h-connector ms-3 mb-3"></div>
                            <div class="h-step mb-3">
                                <label class="sax-label-tiny">SUBCATEGORÍA INTERMEDIA</label>
                                <div class="h-value border-start border-3 ps-3 py-1">
                                    {{ $categoriasfilhas->subcategory->name ?? 'Sin subcategoría' }}
                                </div>
                            </div>
                            <div class="h-connector ms-3 mb-3"></div>
                            <div class="h-step">
                                <label class="sax-label-tiny text-dark fw-bolder">NOMBRE TERMINAL</label>
                                <div class="h-value border-start border-3 border-dark ps-3 py-1 fw-bold fs-5">
                                    {{ $categoriasfilhas->name }}
                                </div>
                            </div>
                        </div>

                        <div class="technical-details pt-4 border-top">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="sax-label-tiny">ID ÚNICO</label>
                                    <div class="fw-bold">#{{ $categoriasfilhas->id }}</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="sax-label-tiny">SLUG / URL</label>
                                    <div class="text-muted italic small">/{{ $categoriasfilhas->slug ?? 'n-a' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('admin.categorias-filhas.edit', $categoriasfilhas) }}"
                                class="btn btn-dark w-100 rounded-pill fw-bold x-small py-3">
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
                                    @if ($categoriasfilhas->photo)
                                        <img src="{{ asset('storage/' . $categoriasfilhas->photo) }}" class="img-fluid">
                                    @else
                                        <div class="empty-media"><i class="fas fa-image mb-2"></i><span>No cargado</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Banner --}}
                            <div class="col-md-7">
                                <label class="sax-label d-block mb-3 text-center">BANNER PUBLICITARIO</label>
                                <div class="media-preview-sax banner-height shadow-sm">
                                    @if ($categoriasfilhas->banner)
                                        <img src="{{ asset('storage/' . $categoriasfilhas->banner) }}" class="img-fluid">
                                    @else
                                        <div class="empty-media"><i class="fas fa-images mb-2"></i><span>Sin banner</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light mt-5 border rounded-4">
                            <div class="d-flex gap-3 align-items-center">
                                <i class="fas fa-info-circle text-dark fs-4"></i>
                                <div class="small text-muted">
                                    Las imágenes mostradas aquí representan el nivel final de navegación. Asegúrese de que
                                    el estilo visual sea coherente con la <strong>Categoría Raíz</strong> para mantener la
                                    armonía del catálogo.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
