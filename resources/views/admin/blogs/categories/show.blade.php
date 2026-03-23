@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header de Navegação --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Categoría de Blog</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">Detalles del recurso informativo</span>
        </div>
        <a href="{{ route('admin.blog-categories.index') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="sax-premium-card border-0 shadow-sm overflow-hidden">
                {{-- Preview do Banner --}}
                <div class="banner-preview-wrapper bg-light">
                    @if($category->banner && file_exists(storage_path('app/public/' . $category->banner)))
                        <img src="{{ asset('storage/' . $category->banner) }}" class="banner-display-img">
                        <div class="banner-overlay">
                            <span class="badge bg-white text-dark rounded-pill px-3 shadow-sm">Vista Previa</span>
                        </div>
                    @else
                        <div class="banner-empty-state py-5 text-center">
                            <i class="far fa-image fa-3x mb-3 opacity-25"></i>
                            <p class="text-muted text-uppercase letter-spacing-1 small mb-0">Sin banner asignado</p>
                        </div>
                    @endif
                </div>

                {{-- Informações Técnicas --}}
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <label class="sax-label mb-1">NOMBRE DE LA CATEGORÍA</label>
                            <h3 class="fw-bold text-dark mb-0">{{ $category->name }}</h3>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <span class="badge bg-soft-dark text-dark border px-3 py-2">
                                ID: #{{ $category->id ?? '0' }}
                            </span>
                        </div>
                    </div>

                    <hr class="my-4 opacity-10">

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('admin.blog-categories.edit', $category->id) }}" class="btn btn-dark rounded-pill px-4 btn-sm fw-bold">
                            <i class="fas fa-edit me-2"></i> EDITAR CATEGORÍA
                        </a>
                        {{-- Opcional: Link para ver no blog --}}
                        <a href="#" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">
                            <i class="fas fa-external-link-alt me-2"></i> VER EN BLOG
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
