@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Editar Categoria"
        description="Gerencie as informações principais e identidade visual da categoria <strong>{{ $category->name }}</strong>">
        <x-slot:actions>
            <a href="{{ route('admin.categories.index') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
            </a>
        </x-slot:actions>
    </x-admin.page-header>

        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="sax-premium-card shadow-sm border-0">
                    <div class="card-sax-header border-bottom p-4 bg-light rounded-top-4">
                        <h6 class="m-0 fw-bold letter-spacing-1 text-muted x-small">FORMULARIO DE CONFIGURACIÓN</h6>
                    </div>

                    <div class="card-sax-body p-4 p-md-5">
                        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" id="main-edit-form" class="sax-form">
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

{{-- Formulários Ocultos para Delete --}}
@if ($category->photo)
<form id="delete-photo-form" action="{{ route('admin.categories.deletePhoto', $category->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif
@if ($category->banner)
<form id="delete-banner-form" action="{{ route('admin.categories.deleteBanner', $category->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif
</x-admin.card>
@endsection
