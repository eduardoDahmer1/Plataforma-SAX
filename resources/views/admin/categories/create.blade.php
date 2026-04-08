@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Nueva Categoría"
        description="Defina un nuevo departamento para la tienda">
        <x-slot:actions>
            <a href="{{ route('admin.categories.index') }}" class="btn-back-minimal">
                <i class="fas fa-arrow-left me-1"></i> VOLVER
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="sax-form">
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
</x-admin.card>

@endsection
