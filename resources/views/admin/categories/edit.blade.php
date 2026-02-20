@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-4">
    <div class="container-fluid">
        {{-- Header --}}
        <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Editar Categoria</h2>
                <div class="sax-divider-dark"></div>
                <p class="text-muted small mt-2">Gerencie as informações principais e identidade visual da categoria <strong>{{ $category->name }}</strong></p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="sax-premium-card shadow-sm border-0">
                    <div class="card-sax-header border-bottom p-4 bg-light rounded-top-4">
                        <h6 class="m-0 fw-bold letter-spacing-1 text-muted x-small">FORMULARIO DE CONFIGURACIÓN</h6>
                    </div>

                    <div class="card-sax-body p-4 p-md-5">
                        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" id="main-edit-form">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                {{-- Lado Esquerdo: Dados --}}
                                <div class="col-lg-7">
                                    <div class="mb-4">
                                        <label for="name" class="sax-label-tiny mb-2">NOMBRE DE LA CATEGORÍA</label>
                                        <div class="input-group-sax">
                                            <span class="input-icon"><i class="fas fa-tag"></i></span>
                                            <input type="text" class="form-control-sax @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name ?? '') }}" required placeholder="Ex: Bebidas">
                                        </div>
                                        @error('name') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="slug" class="sax-label-tiny mb-2">URL AMIGABLE (SLUG)</label>
                                        <div class="input-group-sax">
                                            <span class="input-icon"><i class="fas fa-link"></i></span>
                                            <input type="text" class="form-control-sax @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $category->slug ?? '') }}" required placeholder="ex-bebidas-importadas">
                                        </div>
                                        @error('slug') <div class="text-danger x-small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                {{-- Lado Direito: Uploads --}}
                                <div class="col-lg-5">
                                    <div class="row g-3">
                                        {{-- Foto --}}
                                        <div class="col-6 col-lg-12">
                                            <label class="sax-label-tiny mb-2 d-block text-center">FOTO (LOGO)</label>
                                            <div class="media-upload-preview shadow-sm mx-auto">
                                                @if ($category->photo)
                                                    <img src="{{ asset('storage/' . $category->photo) }}" id="preview-photo">
                                                    <button type="button" onclick="confirmDelete('photo')" class="btn-delete-media"><i class="fas fa-times"></i></button>
                                                @else
                                                    <div class="empty-upload"><i class="fas fa-cloud-upload-alt"></i></div>
                                                @endif
                                                <input type="file" name="photo" class="input-overlay" accept="image/*" onchange="previewImg(this, 'preview-photo')">
                                            </div>
                                        </div>

                                        {{-- Banner --}}
                                        <div class="col-6 col-lg-12">
                                            <label class="sax-label-tiny mb-2 d-block text-center">BANNER DASHBOARD</label>
                                            <div class="media-upload-preview banner-ratio shadow-sm mx-auto">
                                                @if ($category->banner)
                                                    <img src="{{ asset('storage/' . $category->banner) }}" id="preview-banner">
                                                    <button type="button" onclick="confirmDelete('banner')" class="btn-delete-media"><i class="fas fa-times"></i></button>
                                                @else
                                                    <div class="empty-upload"><i class="fas fa-images"></i></div>
                                                @endif
                                                <input type="file" name="banner" class="input-overlay" accept="image/*" onchange="previewImg(this, 'preview-banner')">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-5 pt-4 border-top d-flex flex-wrap justify-content-end gap-3">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold x-small">
                                    <i class="fas fa-times me-2"></i>CANCELAR
                                </a>
                                <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold x-small py-3">
                                    <i class="fas fa-sync-alt me-2 text-warning"></i>ACTUALIZAR DATOS
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Formulários Ocultos para Delete --}}
@if ($category->photo)
<form id="delete-photo-form" action="{{ route('admin.categories.deletePhoto', $category->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif
@if ($category->banner)
<form id="delete-banner-form" action="{{ route('admin.categories.deleteBanner', $category->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif

<script>
    function confirmDelete(type) {
        if(confirm('¿Desea eliminar esta imagen?')) {
            document.getElementById('delete-' + type + '-form').submit();
        }
    }

    function previewImg(input, targetId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById(targetId);
                if(img) img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
<style>
    /* Container Base */
.sax-admin-container {
    background-color: #f8f9fa;
    min-height: 100vh;
}

/* Tipografia e Títulos */
.sax-title {
    font-size: 1.4rem;
    font-weight: 900;
    color: #1a1a1a;
}

.sax-divider-dark {
    width: 40px;
    height: 4px;
    background: #1a1a1a;
    margin-top: 5px;
}

.sax-label-tiny {
    font-size: 0.65rem;
    font-weight: 800;
    color: #999;
    letter-spacing: 1px;
    display: block;
}

.x-small { font-size: 0.75rem; }

/* Inputs Customizados */
.input-group-sax {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 15px;
    color: #ccc;
    font-size: 0.9rem;
}

.form-control-sax {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 1.5px solid #eee;
    border-radius: 12px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: #fff;
}

.form-control-sax:focus {
    border-color: #1a1a1a;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    outline: none;
}

/* Área de Upload e Previews */
.media-upload-preview {
    width: 140px;
    height: 140px;
    background: #fdfdfd;
    border: 2px dashed #eee;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.media-upload-preview.banner-ratio {
    width: 100%;
    max-width: 280px;
    height: 120px;
}

.media-upload-preview:hover {
    border-color: #1a1a1a;
    background: #f8f8f8;
}

.media-upload-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.input-overlay {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    z-index: 2;
}

.empty-upload {
    color: #ddd;
    font-size: 1.5rem;
}

.btn-delete-media {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #ff4d4d;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    font-size: 0.7rem;
    z-index: 3;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Botões e Utilitários */
.sax-premium-card {
    background: #fff;
    border-radius: 24px;
}

.btn-back-minimal {
    text-decoration: none;
    color: #666;
    font-weight: 800;
    font-size: 0.7rem;
    letter-spacing: 1px;
    transition: color 0.3s;
}

.btn-back-minimal:hover { color: #000; }

@media (max-width: 768px) {
    .media-upload-preview { width: 100%; }
    .card-sax-body { padding: 25px !important; }
}
</style>