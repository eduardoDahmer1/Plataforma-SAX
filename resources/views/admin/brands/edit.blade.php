@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Editar Marca</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">ID da Marca: #{{ $brand->id }}</span>
        </div>
        <a href="{{ route('admin.brands.index') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> DESCARTAR CAMBIOS
        </a>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- Coluna de Texto --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">INFORMACIÓN GENERAL</h6>
                            
                            <div class="mb-4">
                                <label for="name" class="sax-form-label">Nombre Comercial</label>
                                <input type="text" 
                                       class="form-control sax-input @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name', $brand->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="sax-form-label">Slug (URL Amistosa)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 x-small fw-bold">sax.com.py/</span>
                                    <input type="text" 
                                           class="form-control sax-input @error('slug') is-invalid @enderror" 
                                           id="slug" name="slug" 
                                           value="{{ old('slug', $brand->slug) }}" required>
                                </div>
                                @error('slug') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Coluna de Mídia --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">IDENTIDAD VISUAL</h6>

                            <div class="mb-4">
                                <label class="sax-form-label">Logotipo de la Marca</label>
                                <div class="asset-upload-box">
                                    @if ($brand->image)
                                        <div class="current-asset-preview mb-3">
                                            <img src="{{ asset('storage/' . $brand->image) }}" class="img-fluid rounded border">
                                            <button type="button" class="btn-remove-asset shadow-sm" onclick="event.preventDefault(); if(confirm('¿Eliminar logo?')) document.getElementById('delete-logo-form').submit();">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <input type="file" name="image" class="form-control sax-input-file" accept="image/*">
                                    <small class="text-muted x-small mt-1 d-block">Formato recomendado: PNG transparente (300x300px)</small>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="sax-form-label">Banner Publicitario</label>
                                <div class="asset-upload-box">
                                    @if ($brand->banner)
                                        <div class="current-asset-preview mb-3">
                                            <img src="{{ asset('storage/' . $brand->banner) }}" class="img-fluid rounded border">
                                            <button type="button" class="btn-remove-asset shadow-sm" onclick="event.preventDefault(); if(confirm('¿Eliminar banner?')) document.getElementById('delete-banner-form').submit();">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <input type="file" name="banner" class="form-control sax-input-file" accept="image/*">
                                    <small class="text-muted x-small mt-1 d-block">Formato recomendado: JPG/WebP (1920x400px)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botões de Ação --}}
                <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.index') }}" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">
                            <i class="fas fa-home me-2"></i> INICIO
                        </a>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-light rounded-pill px-4 btn-sm fw-bold text-muted">CANCELAR</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-5 btn-sm fw-bold">
                            <i class="fas fa-save me-2 text-warning"></i> GUARDAR CAMBIOS
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Forms auxiliares fora do loop principal --}}
@if ($brand->image)
<form id="delete-logo-form" action="{{ route('admin.brands.deleteLogo', $brand->id) }}" method="POST" class="d-none">
    @csrf @method('DELETE')
</form>
@endif

@if ($brand->banner)
<form id="delete-banner-form" action="{{ route('admin.brands.deleteBanner', $brand->id) }}" method="POST" class="d-none">
    @csrf @method('DELETE')
</form>
@endif

@endsection
<style>
    /* Inputs Customizados */
.sax-input {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 15px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.sax-input:focus {
    border-color: #000;
    box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
}

.sax-form-label {
    font-size: 0.7rem;
    font-weight: 800;
    color: #64748b;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    text-transform: uppercase;
}

/* Asset Management Box */
.asset-upload-box {
    background: #fcfcfc;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 15px;
}

.current-asset-preview {
    position: relative;
    max-width: 180px;
}

.current-asset-preview img {
    background: #fff;
    padding: 5px;
}

.btn-remove-asset {
    position: absolute;
    top: -10px;
    right: -10px;
    background: #ef4444;
    color: #fff;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    transition: 0.3s;
}

.btn-remove-asset:hover {
    background: #b91c1c;
    transform: scale(1.1);
}

.sax-input-file {
    font-size: 0.75rem;
    border: none;
    background: transparent;
    padding: 0;
}

/* Helpers */
.sax-premium-card {
    background: #fff;
    border-radius: 18px;
}

.x-small { font-size: 0.65rem; }
</style>