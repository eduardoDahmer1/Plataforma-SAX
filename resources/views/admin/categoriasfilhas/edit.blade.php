@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Editar Nivel Final"
        description="Sub-Subcategoría: <strong>{{ $categoriasfilhas->name }}</strong>">
        <x-slot:actions>
            <a href="{{ route('admin.categorias-filhas.index') }}" class="btn-back-minimal">
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
                    <form action="{{ route('admin.categorias-filhas.update', ['categorias_filha' => $categoriasfilhas->id]) }}" method="POST" enctype="multipart/form-data" class="sax-form">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            {{-- Datos --}}
                            <div class="col-lg-7">
                                <div class="mb-4">
                                    <label for="name" class="sax-label-tiny mb-2">NOMBRE DEL NIVEL FINAL</label>
                                    <div class="input-group-sax">
                                        <span class="input-icon"><i class="fas fa-tag"></i></span>
                                        <input type="text"
                                               class="form-control-sax @error('name') is-invalid @enderror"
                                               id="name" name="name"
                                               value="{{ old('name', $categoriasfilhas->name) }}"
                                               required
                                               placeholder="Ej: Zapatillas Running">
                                    </div>
                                    @error('name')
                                        <div class="text-danger x-small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="subcategory_id" class="sax-label-tiny mb-2">SUBCATEGORÍA PADRE</label>
                                    <div class="input-group-sax">
                                        <span class="input-icon"><i class="fas fa-sitemap"></i></span>
                                        <select id="subcategory_id" name="subcategory_id"
                                                class="form-control-sax @error('subcategory_id') is-invalid @enderror"
                                                required>
                                            <option value="">Seleccione una subcategoría</option>
                                            @foreach ($subcategories as $subcategory)
                                                <option value="{{ $subcategory->id }}"
                                                    {{ old('subcategory_id', $categoriasfilhas->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                                    {{ $subcategory->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('subcategory_id')
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
                                            label="FOTO DE REFERENCIA"
                                            :current="$categoriasfilhas->photo"
                                            :uploadUrl="route('admin.categorias-filhas.uploadPhoto', $categoriasfilhas->id)"
                                            :showDelete="true"
                                            ratio="square" />
                                    </div>
                                    <div class="col-6 col-lg-12">
                                        <x-admin.media-field
                                            field="banner"
                                            label="BANNER DE SECCIÓN"
                                            :current="$categoriasfilhas->banner"
                                            :uploadUrl="route('admin.categorias-filhas.uploadBanner', $categoriasfilhas->id)"
                                            :showDelete="true"
                                            ratio="banner" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <x-admin.form-actions
                            :cancelRoute="route('admin.categorias-filhas.index')"
                            cancelLabel="Cancelar"
                            submitLabel="Guardar cambios"
                            submitIcon="fa-save" />
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Forms de borrado: fuera del form principal para evitar anidamiento inválido --}}
    <form id="delete-photo-form" action="{{ route('admin.categorias-filhas.deletePhoto', $categoriasfilhas->id) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>
    <form id="delete-banner-form" action="{{ route('admin.categorias-filhas.deleteBanner', $categoriasfilhas->id) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>

</x-admin.card>
@endsection
