@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2>Editar Marca</h2>

    {{-- FORM PRINCIPAL --}}
    <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nome da Marca</label>
            <input type="text" 
                   class="form-control @error('name') is-invalid @enderror" 
                   id="name" 
                   name="name" 
                   value="{{ old('name', $brand->name) }}" 
                   required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Slug --}}
        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" 
                   class="form-control @error('slug') is-invalid @enderror" 
                   id="slug" 
                   name="slug" 
                   value="{{ old('slug', $brand->slug) }}" 
                   required>
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Logo --}}
        <div class="mb-3">
            <label class="form-label">Logo da Marca</label>
            @if ($brand->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $brand->image) }}" alt="Logo da Marca" width="150">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('delete-logo-form').submit();" class="btn btn-sm btn-danger">Excluir Logo</a>
                </div>
            @endif
            <input type="file" name="image" class="form-control mt-2" accept="image/*">
            @error('image')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Banner --}}
        <div class="mb-3">
            <label class="form-label">Banner</label>
            @if ($brand->banner)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $brand->banner) }}" alt="Banner da Marca" width="150">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('delete-banner-form').submit();" class="btn btn-sm btn-danger">Excluir Banner</a>
                </div>
            @endif
            <input type="file" name="banner" class="form-control mt-2" accept="image/*">
            @error('banner')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Botões --}}
        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Cancelar</a>
        <a href="{{ route('admin.index') }}" class="btn btn-secondary">Admin</a>
    </form>

    {{-- FORM DELETE LOGO --}}
    @if ($brand->image)
    <form id="delete-logo-form" action="{{ route('admin.brands.deleteLogo', $brand->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

    {{-- FORM DELETE BANNER --}}
    @if ($brand->banner)
    <form id="delete-banner-form" action="{{ route('admin.brands.deleteBanner', $brand->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

</div>
@endsection
