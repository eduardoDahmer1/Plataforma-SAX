@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-folder me-2"></i>Editar Categoria</h2>

    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label"><i class="fas fa-tag me-1"></i>Nome</label>
            <input type="text" 
                   class="form-control @error('name') is-invalid @enderror" 
                   id="name" 
                   name="name" 
                   value="{{ old('name', isset($category->name) ? $category->name : '') }}" 
                   required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Slug --}}
        <div class="mb-3">
            <label for="slug" class="form-label"><i class="fas fa-link me-1"></i>Slug</label>
            <input type="text" 
                   class="form-control @error('slug') is-invalid @enderror" 
                   id="slug" 
                   name="slug" 
                   value="{{ old('slug', isset($category->slug) ? $category->slug : '') }}" 
                   required>
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Foto (Logo) --}}
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-image me-1"></i>Foto (Logo)</label>
            @if ($category->photo)
                <div class="mb-2 position-relative d-inline-block">
                    <img src="{{ asset('storage/' . $category->photo) }}" alt="Logo" class="img-thumbnail" style="max-width:150px;">
                    <button onclick="event.preventDefault(); document.getElementById('delete-photo-form').submit();" 
                            class="btn btn-sm btn-danger position-absolute top-0 end-0">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            <input type="file" name="photo" class="form-control mt-2" accept="image/*">
            @error('photo')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Banner --}}
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-images me-1"></i>Banner</label>
            @if ($category->banner)
                <div class="mb-2 position-relative d-inline-block">
                    <img src="{{ asset('storage/' . $category->banner) }}" alt="Banner" class="img-thumbnail" style="max-width:150px;">
                    <button onclick="event.preventDefault(); document.getElementById('delete-banner-form').submit();" 
                            class="btn btn-sm btn-danger position-absolute top-0 end-0">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            <input type="file" name="banner" class="form-control mt-2" accept="image/*">
            @error('banner')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Botões --}}
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Atualizar</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Cancelar</a>
            <a href="{{ route('admin.index') }}" class="btn btn-secondary"><i class="fas fa-home me-1"></i>Admin</a>
        </div>
    </form>

    {{-- DELETE FORM --}}
    @if ($category->photo)
    <form id="delete-photo-form" action="{{ route('admin.categories.deletePhoto', $category->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

    @if ($category->banner)
    <form id="delete-banner-form" action="{{ route('admin.categories.deleteBanner', $category->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif
</div>
@endsection
