@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2">
    {{-- Header --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Editar Nivel Final</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">Sub-Subcategoría: <span class="text-dark fw-bold">#{{ $categoriasfilhas->id }}</span></span>
        </div>
        <a href="{{ route('admin.categorias-filhas.index') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
        </a>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form action="{{ route('admin.categorias-filhas.update', ['categorias_filha' => $categoriasfilhas->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    {{-- Coluna 1: Estrutura --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">DEFINICIÓN Y JERARQUÍA</h6>
                            
                            {{-- Nome --}}
                            <div class="mb-4">
                                <label class="sax-form-label">Nombre del Nivel Final</label>
                                <input type="text" name="name" class="form-control sax-input @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $categoriasfilhas->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Subcategoria Pai --}}
                            <div class="mb-3">
                                <label class="sax-form-label">Subcategoría de Origen (Padre)</label>
                                <div class="sax-select-wrapper">
                                    <select name="subcategory_id" class="form-select sax-input @error('subcategory_id') is-invalid @enderror" required>
                                        <option value="">Seleccione una subcategoría</option>
                                        @foreach ($subcategories as $subcategory)
                                            <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $categoriasfilhas->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                                {{ $subcategory->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-sitemap select-icon opacity-50"></i>
                                </div>
                                @error('subcategory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Coluna 2: Mídia --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">GESTIÓN DE ACTIVOS</h6>

                            {{-- Foto --}}
                            <div class="mb-4">
                                <label class="sax-form-label">Foto de Referencia</label>
                                <div class="asset-edit-box">
                                    @if ($categoriasfilhas->photo)
                                        <div class="asset-preview-sm mb-3">
                                            <img src="{{ asset('storage/' . $categoriasfilhas->photo) }}" class="rounded shadow-sm border">
                                            <button type="button" onclick="event.preventDefault(); if(confirm('¿Eliminar foto?')) document.getElementById('delete-photo-form').submit();" class="btn-del-mini">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <input type="file" name="photo" class="form-control sax-input-file">
                                </div>
                            </div>

                            {{-- Banner --}}
                            <div class="mb-0">
                                <label class="sax-form-label">Banner de Sección</label>
                                <div class="asset-edit-box">
                                    @if ($categoriasfilhas->banner)
                                        <div class="asset-preview-sm mb-3">
                                            <img src="{{ asset('storage/' . $categoriasfilhas->banner) }}" class="rounded shadow-sm border w-100">
                                            <button type="button" onclick="event.preventDefault(); if(confirm('¿Eliminar banner?')) document.getElementById('delete-banner-form').submit();" class="btn-del-mini">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <input type="file" name="banner" class="form-control sax-input-file">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ações --}}
                <div class="mt-5 pt-4 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.categorias-filhas.index') }}" class="btn btn-light rounded-pill px-4 fw-bold text-muted x-small">CANCELAR</a>
                    <button type="submit" class="btn btn-dark rounded-pill px-5 fw-bold letter-spacing-1">
                        ACTUALIZAR DATOS <i class="fas fa-sync-alt ms-2 text-warning"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden Forms --}}
@if ($categoriasfilhas->photo)
<form id="delete-photo-form" action="{{ route('admin.categoriasfilhas.deletePhoto', $categoriasfilhas->id) }}" method="POST" class="d-none">
    @csrf @method('DELETE')
</form>
@endif

@if ($categoriasfilhas->banner)
<form id="delete-banner-form" action="{{ route('admin.categoriasfilhas.deleteBanner', $categoriasfilhas->id) }}" method="POST" class="d-none">
    @csrf @method('DELETE')
</form>
@endif
@endsection
