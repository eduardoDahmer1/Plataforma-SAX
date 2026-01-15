@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Nueva Categoría</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">Defina un nuevo departamento para la tienda</span>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn-back-minimal">
            <i class="fas fa-arrow-left me-1"></i> VOLVER
        </a>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    
                    {{-- Coluna: Definições de Texto --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">ESTRUCTURA DE TEXTO</h6>
                            
                            <div class="mb-4">
                                <label for="name" class="sax-form-label">Nombre del Departamento</label>
                                <input type="text" class="form-control sax-input" id="name" name="name" 
                                       placeholder="Ej: Relojes de Lujo" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="sax-form-label">Slug (URL)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 x-small text-muted">/cat/</span>
                                    <input type="text" class="form-control sax-input" id="slug" name="slug" 
                                           placeholder="relojes-de-lujo" required>
                                </div>
                                <small class="text-muted x-small mt-2 d-block">Generado automáticamente para SEO.</small>
                            </div>
                        </div>
                    </div>

                    {{-- Coluna: Ativos de Mídia --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">IMÁGENES DEL DEPARTAMENTO</h6>

                            <div class="mb-4">
                                <label for="photo" class="sax-form-label">Foto de Portada (Miniatura)</label>
                                <div class="sax-file-dropzone">
                                    <i class="fas fa-camera mb-2 opacity-25"></i>
                                    <input type="file" class="form-control sax-file-input" name="photo" accept="image/*">
                                    <p class="x-small text-muted mb-0">Haga clic o arrastre imagen (cuadrada)</p>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label for="banner" class="sax-form-label">Banner Principal</label>
                                <div class="sax-file-dropzone">
                                    <i class="fas fa-images mb-2 opacity-25"></i>
                                    <input type="file" class="form-control sax-file-input" name="banner" accept="image/*">
                                    <p class="x-small text-muted mb-0">Recomendado: 1920x400px</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer de Ações --}}
                <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.index') }}" class="btn btn-link text-muted text-decoration-none x-small fw-bold">
                        <i class="fas fa-home me-1"></i> DASHBOARD
                    </a>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light rounded-pill px-4 x-small fw-bold border">CANCELAR</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold letter-spacing-1">
                            GUARDAR CATEGORÍA <i class="fas fa-check-circle ms-2 text-success"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script de Automação de Slug --}}
<script>
    document.getElementById('name').addEventListener('input', function() {
        let slug = this.value.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, "")
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        document.getElementById('slug').value = slug;
    });
</script>
@endsection
<style>
    /* Zona de Upload Minimalista */
.sax-file-dropzone {
    border: 2px dashed #e9ecef;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    background: #fcfcfc;
    position: relative;
    transition: all 0.3s ease;
}

.sax-file-dropzone:hover {
    border-color: #000;
    background: #fff;
}

.sax-file-input {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    opacity: 0;
    cursor: pointer;
}

/* Campos de Texto */
.sax-input {
    border: 1px solid #f1f4f8;
    background: #fff;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 0.9rem;
}

.sax-input:focus {
    border-color: #000;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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