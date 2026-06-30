@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Editar Subcategoría"
        description="Subcategoría: <strong>{{ $subcategory->name }}</strong>">
        <x-slot:actions>
            <a href="{{ route('admin.subcategories.index') }}" class="btn-back-minimal">
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
                    <form action="{{ route('admin.subcategories.update', $subcategory->id) }}" method="POST" enctype="multipart/form-data" class="sax-form">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            {{-- Datos --}}
                            <div class="col-lg-7">
                                <div class="mb-4">
                                    <label for="name" class="sax-label-tiny mb-2">NOMBRE DE LA SUBCATEGORÍA</label>
                                    <div class="input-group-sax">
                                        <span class="input-icon"><i class="fas fa-tag"></i></span>
                                        <input type="text"
                                               class="form-control-sax @error('name') is-invalid @enderror"
                                               id="name" name="name"
                                               value="{{ old('name', $subcategory->name) }}"
                                               required
                                               placeholder="Ej: Calzado Deportivo">
                                    </div>
                                    @error('name')
                                        <div class="text-danger x-small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="category_id" class="sax-label-tiny mb-2">CATEGORÍA PADRE</label>
                                    <div class="input-group-sax">
                                        <span class="input-icon"><i class="fas fa-sitemap"></i></span>
                                        <select id="category_id" name="category_id"
                                                class="form-control-sax @error('category_id') is-invalid @enderror"
                                                required>
                                            <option value="">Seleccione una categoría</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name ?: $category->slug }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('category_id')
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
                                            :current="$subcategory->photo"
                                            :uploadUrl="route('admin.subcategories.uploadPhoto', $subcategory->id)"
                                            :showDelete="true"
                                            ratio="square" />
                                    </div>
                                    <div class="col-6 col-lg-12">
                                        <x-admin.media-field
                                            field="banner"
                                            label="BANNER"
                                            :current="$subcategory->banner"
                                            :uploadUrl="route('admin.subcategories.uploadBanner', $subcategory->id)"
                                            :showDelete="true"
                                            ratio="banner" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <x-admin.form-actions
                            :cancelRoute="route('admin.subcategories.index')"
                            cancelLabel="Cancelar"
                            submitLabel="Guardar cambios"
                            submitIcon="fa-save" />
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Forms de borrado: fuera del form principal para evitar anidamiento inválido --}}
    <form id="delete-photo-form" action="{{ route('admin.subcategories.deletePhoto', $subcategory->id) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>
    <form id="delete-banner-form" action="{{ route('admin.subcategories.deleteBanner', $subcategory->id) }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>

</x-admin.card>
@endsection
