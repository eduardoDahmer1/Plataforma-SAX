@extends('layout.admin')

@section('content')
<x-admin.card>
    {{-- Navegação e Título --}}
    <div class="mb-5">
        <a href="{{ route('admin.blog-categories.index') }}" class="text-decoration-none x-small fw-bold text-uppercase text-secondary tracking-wider">
            <i class="fa fa-chevron-left me-1"></i> Directorio de Categorías
        </a>
        <h1 class="h4 fw-light mt-2 mb-0 text-uppercase tracking-wider">Nueva Categoría Editorial</h1>
        <div class="sax-divider-dark mt-3"></div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <form action="{{ route('admin.blog-categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Campo: Nome --}}
                <div class="mb-5">
                    <label for="name" class="sax-form-label">Identificación de la Categoría</label>
                    <input type="text" name="name" id="name" class="form-control sax-input" 
                           placeholder="Ej: Tendencias 2026, Estilo de Vida..." value="{{ old('name') }}" required>
                    <small class="text-muted x-small mt-2 d-block italic">Este nombre será visible para los lectores en el blog.</small>
                </div>

                {{-- Campo: Banner (Upload Minimalista) --}}
                <div class="mb-5">
                    <label class="sax-form-label">Banner de Cabecera</label>
                    <div class="sax-upload-zone position-relative d-flex flex-column align-items-center justify-content-center border-dashed py-5 px-3">
                        <i class="fa fa-cloud-upload-alt text-muted mb-3 fs-4"></i>
                        <input type="file" name="banner" id="banner" class="sax-file-input" accept="image/*">
                        <div class="text-center">
                            <span class="x-small fw-bold text-uppercase d-block mb-1">Seleccionar Archivo</span>
                            <span class="x-small text-muted italic">Formatos: JPG, PNG o WEBP (Recomendado: 1200x400px)</span>
                        </div>
                    </div>
                </div>

                {{-- Botões de Ação --}}
                <div class="border-top pt-4 mt-5 d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-dark rounded-0 px-5 py-2 fw-bold text-uppercase tracking-wider small">
                        Crear Categoría
                    </button>
                    <a href="{{ route('admin.blog-categories.index') }}" class="text-secondary text-decoration-none x-small fw-bold text-uppercase hover-underline">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        {{-- Coluna Lateral Informativa --}}
        <div class="col-lg-4 offset-lg-1 d-none d-lg-block">
            <div class="border-start ps-4 h-100">
                <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3">Recomendaciones</h6>
                <p class="x-small text-secondary lh-lg italic">
                    Las categorías ayudan a organizar sus artículos para que los lectores encuentren contenido relevante rápidamente. Utilice nombres cortos y descriptivos. El banner se utilizará como fondo en la página de listado de la categoría.
                </p>
            </div>
        </div>
    </div>

</x-admin.card>

@endsection