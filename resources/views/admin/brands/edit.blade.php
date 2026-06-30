@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Editar Marca"
        description="Marca: <strong>{{ $brand->name }}</strong>">
        <x-slot:actions>
            <a href="{{ route('admin.brands.index') }}" class="btn-back-minimal">
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
                    <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data" class="sax-form">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            {{-- Datos --}}
                            <div class="col-lg-7">
                                <div class="mb-4">
                                    <label for="name" class="sax-label-tiny mb-2">NOMBRE DE LA MARCA</label>
                                    <div class="input-group-sax">
                                        <span class="input-icon"><i class="fas fa-tag"></i></span>
                                        <input type="text"
                                               class="form-control-sax @error('name') is-invalid @enderror"
                                               id="name" name="name"
                                               value="{{ old('name', $brand->name) }}"
                                               required
                                               placeholder="Ej: Nike">
                                    </div>
                                    @error('name')
                                        <div class="text-danger x-small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="slug" class="sax-label-tiny mb-2">SLUG DE NAVEGACIÓN</label>
                                    <div class="input-group-sax">
                                        <span class="input-icon"><i class="fas fa-link"></i></span>
                                        <input type="text"
                                               class="form-control-sax @error('slug') is-invalid @enderror"
                                               id="slug" name="slug"
                                               value="{{ old('slug', $brand->slug) }}"
                                               required
                                               placeholder="nike">
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
                                            field="image"
                                            label="LOGOTIPO"
                                            :current="$brand->image"
                                            :uploadUrl="route('admin.brands.uploadLogo', $brand->id)"
                                            :showDelete="true"
                                            ratio="square" />
                                    </div>
                                    <div class="col-6 col-lg-12">
                                        <x-admin.media-field
                                            field="banner"
                                            label="BANNER PROMOCIONAL"
                                            :current="$brand->banner"
                                            :uploadUrl="route('admin.brands.uploadBanner', $brand->id)"
                                            :showDelete="true"
                                            ratio="banner" />
                                    </div>
                                    <div class="col-6 col-lg-12">
                                        <x-admin.media-field
                                            field="internal_banner"
                                            label="BANNER INTERNO"
                                            :current="$brand->internal_banner"
                                            :uploadUrl="route('admin.brands.uploadInternalBanner', $brand->id)"
                                            :showDelete="true"
                                            ratio="banner" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <x-admin.form-actions
                            :cancelRoute="route('admin.brands.index')"
                            cancelLabel="Cancelar"
                            submitLabel="Guardar cambios"
                            submitIcon="fa-save" />
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Forms de borrado: fuera del form principal para evitar anidamiento inválido --}}
    <form id="delete-image-form" action="{{ route('admin.brands.deleteLogo', $brand->id) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>
    <form id="delete-banner-form" action="{{ route('admin.brands.deleteBanner', $brand->id) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>
    <form id="delete-internal_banner-form" action="{{ route('admin.brands.deleteInternalBanner', $brand->id) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>

</x-admin.card>
@endsection
