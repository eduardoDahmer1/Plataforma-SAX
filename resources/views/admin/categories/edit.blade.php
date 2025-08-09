@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2>Editar Categoria</h2>

    {{-- FORM PRINCIPAL --}}
    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input 
                type="text" 
                class="form-control @error('name') is-invalid @enderror" 
                id="name" 
                name="name" 
                value="{{ old('name', $category->name) }}" 
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Slug --}}
        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input 
                type="text" 
                class="form-control @error('slug') is-invalid @enderror" 
                id="slug" 
                name="slug" 
                value="{{ old('slug', $category->slug) }}" 
                required
            >
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Foto (Logo) --}}
        <div class="mb-3">
            <label class="form-label">Foto (Logo)</label>
            @if ($category->photo)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $category->photo) }}" alt="Logo" width="150">
                    <a href="#" 
                       onclick="event.preventDefault(); document.getElementById('delete-photo-form').submit();" 
                       class="btn btn-sm btn-danger">Excluir Foto</a>
                </div>
            @endif
            <input type="file" name="photo" class="form-control mt-2" accept="image/*">
            @error('photo')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Banner --}}
        <div class="mb-3">
            <label class="form-label">Banner</label>
            @if ($category->banner)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $category->banner) }}" alt="Banner" width="150">
                    <a href="#" 
                       onclick="event.preventDefault(); document.getElementById('delete-banner-form').submit();" 
                       class="btn btn-sm btn-danger">Excluir Banner</a>
                </div>
            @endif
            <input type="file" name="banner" class="form-control mt-2" accept="image/*">
            @error('banner')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Botões --}}
        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancelar</a>
        <a href="{{ route('admin.index') }}" class="btn btn-secondary">Admin</a>
    </form>

    {{-- FORM DELETE FOTO --}}
    @if ($category->photo)
    <form id="delete-photo-form" 
          action="{{ route('admin.categories.deletePhoto', $category->id) }}" 
          method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

    {{-- FORM DELETE BANNER --}}
    @if ($category->banner)
    <form id="delete-banner-form" 
          action="{{ route('admin.categories.deleteBanner', $category->id) }}" 
          method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif
</div>
@endsection
