@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-industry me-2"></i>Editar Marca</h2>

    <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Nome -->
        <div class="mb-3">
            <label for="name" class="form-label"><i class="fas fa-tag me-1"></i>Nome da Marca</label>
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

        <!-- Slug -->
        <div class="mb-3">
            <label for="slug" class="form-label"><i class="fas fa-link me-1"></i>Slug</label>
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

        <!-- Logo -->
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-image me-1"></i>Logo da Marca</label>
            @if ($brand->image)
                <div class="mb-2 position-relative d-inline-block">
                    <img src="{{ asset('storage/' . $brand->image) }}" alt="Logo da Marca" class="img-thumbnail" style="max-width:150px;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="event.preventDefault(); document.getElementById('delete-logo-form').submit();">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            <input type="file" name="image" class="form-control mt-2" accept="image/*">
            @error('image')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Banner -->
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-images me-1"></i>Banner da Marca</label>
            @if ($brand->banner)
                <div class="mb-2 position-relative d-inline-block">
                    <img src="{{ asset('storage/' . $brand->banner) }}" alt="Banner da Marca" class="img-thumbnail" style="max-width:150px;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="event.preventDefault(); document.getElementById('delete-banner-form').submit();">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            <input type="file" name="banner" class="form-control mt-2" accept="image/*">
            @error('banner')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Botões -->
        <div class="mt-4 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Atualizar</button>
            <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Cancelar</a>
            <a href="{{ route('admin.index') }}" class="btn btn-secondary"><i class="fas fa-home me-1"></i>Admin</a>
        </div>
    </form>

    <!-- Form Delete Logo -->
    @if ($brand->image)
    <form id="delete-logo-form" action="{{ route('admin.brands.deleteLogo', $brand->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

    <!-- Form Delete Banner -->
    @if ($brand->banner)
    <form id="delete-banner-form" action="{{ route('admin.brands.deleteBanner', $brand->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

</div>
@endsection
