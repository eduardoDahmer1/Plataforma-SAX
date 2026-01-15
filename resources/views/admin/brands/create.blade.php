@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Nueva Marca</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">Registro de nueva firma en el catálogo</span>
        </div>
        <a href="{{ route('admin.brands.index') }}" class="btn-back-minimal">
            <i class="fas fa-times me-1"></i> CANCELAR
        </a>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data" class="sax-form">
                @csrf

                <div class="row g-4">
                    {{-- Informações Textuais --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">DATOS DE IDENTIFICACIÓN</h6>
                            
                            <div class="mb-4">
                                <label for="name" class="sax-form-label">Nombre de la Marca</label>
                                <input type="text" 
                                       class="form-control sax-input @error('name') is-invalid @enderror" 
                                       id="name" name="name" placeholder="Ej: Gucci"
                                       value="{{ old('name') }}" required autofocus>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="sax-form-label">Slug de Navegación</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 x-small fw-bold text-muted">/marcas/</span>
                                    <input type="text" 
                                           class="form-control sax-input @error('slug') is-invalid @enderror" 
                                           id="slug" name="slug" placeholder="gucci"
                                           value="{{ old('slug') }}" required>
                                </div>
                                <small class="text-muted x-small mt-2 d-block px-1">Se genera automáticamente basado en el nombre.</small>
                                @error('slug') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Upload de Arquivos --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">ARCHIVOS MULTIMEDIA</h6>

                            <div class="mb-4">
                                <label for="image" class="sax-form-label">Logotipo Oficial</label>
                                <div class="asset-upload-zone">
                                    <i class="fas fa-cloud-upload-alt mb-2 opacity-25"></i>
                                    <input type="file" class="form-control sax-input-file @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    <p class="x-small text-muted mb-0">Arrastre o seleccione logo (300x300px)</p>
                                </div>
                                @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-0">
                                <label for="banner" class="sax-form-label">Banner Promocional</label>
                                <div class="asset-upload-zone">
                                    <i class="fas fa-image mb-2 opacity-25"></i>
                                    <input type="file" class="form-control sax-input-file @error('banner') is-invalid @enderror" 
                                           id="banner" name="banner" accept="image/*">
                                    <p class="x-small text-muted mb-0">Recomendado: 1920x400px (JPG/WebP)</p>
                                </div>
                                @error('banner') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ações Inferiores --}}
                <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.index') }}" class="btn btn-link text-dark text-decoration-none x-small fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> DASHBOARD
                    </a>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-light rounded-pill px-4 text-muted x-small fw-bold">DESCARTAR</a>
                        <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold letter-spacing-1">
                            CREAR MARCA <i class="fas fa-check-circle ms-2 text-success"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script para Auto-Slug --}}
<script>
    document.getElementById('name').addEventListener('input', function() {
        let name = this.value;
        let slug = name.toLowerCase()
                       .normalize('NFD').replace(/[\u0300-\u036f]/g, "") // Remove acentos
                       .replace(/[^a-z0-9 -]/g, '') // Remove caracteres especiais
                       .replace(/\s+/g, '-') // Substitui espaços por -
                       .replace(/-+/g, '-'); // Remove hífens duplicados
        document.getElementById('slug').value = slug;
    });
</script>
@endsection
<style>
    /* Estilização da Zona de Upload */
.asset-upload-zone {
    border: 2px dashed #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    background: #fbfbfb;
    transition: all 0.3s ease;
    position: relative;
}

.asset-upload-zone:hover {
    border-color: #000;
    background: #fff;
}

.sax-input-file {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    opacity: 0;
    cursor: pointer;
}

/* Inputs e Estética Premium */
.sax-input {
    border: 1px solid #eef2f7;
    background: #fff;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 0.95rem;
}

.sax-input:focus {
    border-color: #000;
    box-shadow: 0 10px 20px rgba(0,0,0,0.03);
}

.sax-premium-card {
    background: #fff;
    border-radius: 20px;
}

.letter-spacing-1 { letter-spacing: 1px; }

/* Botão Dark SAX */
.btn-dark {
    background: #000;
    border: none;
    padding: 14px 30px;
    font-size: 0.8rem;
    transition: all 0.3s;
}

.btn-dark:hover {
    background: #333;
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.2);
}
</style>