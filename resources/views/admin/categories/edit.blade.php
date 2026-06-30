@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Editar Categoría"
        description="Categoría: <strong>{{ $category->name }}</strong>">
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
                    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data" class="sax-form">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            {{-- Datos --}}
                            <div class="col-lg-7">
                                <div class="mb-4">
                                    <label for="name" class="sax-label-tiny mb-2">NOMBRE DE LA CATEGORÍA</label>
                                    <div class="input-group-sax">
                                        <span class="input-icon"><i class="fas fa-tag"></i></span>
                                        <input type="text"
                                               class="form-control-sax @error('name') is-invalid @enderror"
                                               id="name" name="name"
                                               value="{{ old('name', $category->name ?? '') }}"
                                               required
                                               placeholder="Ej: Bebidas">
                                    </div>
                                    @error('name')
                                        <div class="text-danger x-small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="slug" class="sax-label-tiny mb-2">URL AMIGABLE (SLUG)</label>
                                    <div class="input-group-sax">
                                        <span class="input-icon"><i class="fas fa-link"></i></span>
                                        <input type="text"
                                               class="form-control-sax @error('slug') is-invalid @enderror"
                                               id="slug" name="slug"
                                               value="{{ old('slug', $category->slug ?? '') }}"
                                               required
                                               placeholder="ex-bebidas-importadas">
                                    </div>
                                    @error('slug')
                                        <div class="text-danger x-small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Multimedia --}}
                            <div class="col-lg-5">
                                <div class="row g-3">
                                    <div class="col-6 col-lg-12">
                                        <x-admin.media-field
                                            field="photo"
                                            label="FOTO (LOGO)"
                                            :current="$category->photo"
                                            :uploadUrl="route('admin.categories.uploadPhoto', $category->id)"
                                            :showDelete="true"
                                            ratio="square" />
                                    </div>
                                    <div class="col-6 col-lg-12">
                                        <x-admin.media-field
                                            field="banner"
                                            label="BANNER DASHBOARD"
                                            :current="$category->banner"
                                            :uploadUrl="route('admin.categories.uploadBanner', $category->id)"
                                            :showDelete="true"
                                            ratio="banner" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <x-admin.form-actions
                            :cancelRoute="route('admin.categories.index')"
                            cancelLabel="Cancelar"
                            submitLabel="Guardar cambios"
                            submitIcon="fa-save" />
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Forms de borrado: fuera del form principal para evitar anidamiento inválido --}}
    <form id="delete-photo-form" action="{{ route('admin.categories.deletePhoto', $category->id) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>
    <form id="delete-banner-form" action="{{ route('admin.categories.deleteBanner', $category->id) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>

</x-admin.card>
@endsection
