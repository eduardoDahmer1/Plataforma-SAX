@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Nueva Subcategoría</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">Defina un segundo nivel jerárquico para el catálogo</span>
        </div>
        <a href="{{ route('admin.subcategories.index') }}" class="btn-back-minimal">
            <i class="fas fa-times me-1"></i> DESCARTAR
        </a>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form action="{{ route('admin.subcategories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    
                    {{-- Coluna 1: Posicionamento e Nome --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">UBICACIÓN Y NOMBRE</h6>
                            
                            <div class="mb-4">
                                <label class="sax-form-label">Nombre de Subcategoría</label>
                                <input type="text" name="name" id="name" class="form-control sax-input" 
                                       placeholder="Ej: Calzado Deportivo" value="{{ old('name') }}" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label class="sax-form-label">Vincular a Categoría Principal</label>
                                <div class="sax-select-wrapper">
                                    <select name="category_id" class="form-select sax-input" required>
                                        <option value="" disabled selected>Seleccione el origen...</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name ?: $category->slug }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down select-icon"></i>
                                </div>
                                <small class="text-muted x-small mt-2 d-block px-1">
                                    <i class="fas fa-info-circle me-1"></i> Esto define dónde aparecerá en el menú.
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Coluna 2: Recursos Visuais --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">IMÁGENES DE APOYO</h6>

                            <div class="mb-4">
                                <label class="sax-form-label">Imagen de Icono / Foto</label>
                                <div class="sax-upload-area">
                                    <i class="fas fa-upload mb-2 opacity-25"></i>
                                    <input type="file" name="photo" class="sax-file-hidden">
                                    <p class="x-small text-muted mb-0">Subir foto cuadrada</p>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="sax-form-label">Banner de Subsección</label>
                                <div class="sax-upload-area">
                                    <i class="fas fa-images mb-2 opacity-25"></i>
                                    <input type="file" name="banner" class="sax-file-hidden">
                                    <p class="x-small text-muted mb-0">Sugerido: 1920x400px</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer de Ações --}}
                <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.index') }}" class="btn btn-link text-muted text-decoration-none x-small fw-bold">
                        <i class="fas fa-th-large me-1"></i> PANEL PRINCIPAL
                    </a>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-light rounded-pill px-4 x-small fw-bold border">VOLVER AL LISTADO</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold letter-spacing-1">
                            CREAR SUB-NIVEL <i class="fas fa-save ms-2 text-warning"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
<style>
    /* Custom Select Stylings */
.sax-select-wrapper {
    position: relative;
}

.sax-select-wrapper .select-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    font-size: 0.8rem;
    color: #999;
}

.form-select.sax-input {
    appearance: none;
    cursor: pointer;
}

/* Upload Areas */
.sax-upload-area {
    border: 2px dashed #f0f0f0;
    background: #fafafa;
    border-radius: 12px;
    padding: 30px 15px;
    text-align: center;
    position: relative;
    transition: 0.3s;
}

.sax-upload-area:hover {
    border-color: #000;
    background: #fff;
}

.sax-file-hidden {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    opacity: 0;
    cursor: pointer;
}

/* Base Styles Sync */
.sax-input {
    border: 1px solid #eef2f7;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 0.9rem;
}

.sax-form-label {
    font-size: 0.65rem;
    font-weight: 800;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: block;
}

.sax-premium-card {
    background: #fff;
    border-radius: 20px;
}
</style>