@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-folder me-2"></i>Editar Subcategoria</h2>

    <form action="{{ route('admin.subcategories.update', $subcategory->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label"><i class="fas fa-tag me-1"></i>Nome da Subcategoria</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                   id="name" name="name" value="{{ old('name', $subcategory->name) }}" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label"><i class="fas fa-sitemap me-1"></i>Categoria Pai</label>
            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                <option value="">Selecione uma categoria</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name ?: $category->slug }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Foto --}}
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-image me-1"></i>Foto</label>
            @if ($subcategory->photo)
                <div class="position-relative d-inline-block mb-2">
                    <img src="{{ asset('storage/' . $subcategory->photo) }}" alt="Foto" class="img-thumbnail" style="max-width:150px;">
                    <button onclick="event.preventDefault(); document.getElementById('delete-photo-form').submit();" 
                            class="btn btn-sm btn-danger position-absolute top-0 end-0"><i class="fas fa-times"></i></button>
                </div>
            @endif
            <input type="file" name="photo" class="form-control mt-2">
        </div>

        {{-- Banner --}}
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-images me-1"></i>Banner</label>
            @if ($subcategory->banner)
                <div class="position-relative d-inline-block mb-2">
                    <img src="{{ asset('storage/' . $subcategory->banner) }}" alt="Banner" class="img-thumbnail" style="max-width:150px;">
                    <button onclick="event.preventDefault(); document.getElementById('delete-banner-form').submit();" 
                            class="btn btn-sm btn-danger position-absolute top-0 end-0"><i class="fas fa-times"></i></button>
                </div>
            @endif
            <input type="file" name="banner" class="form-control mt-2">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
            <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Cancelar</a>
        </div>
    </form>

    @if ($subcategory->photo)
    <form id="delete-photo-form" action="{{ route('admin.subcategories.deletePhoto', $subcategory->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

    @if ($subcategory->banner)
    <form id="delete-banner-form" action="{{ route('admin.subcategories.deleteBanner', $subcategory->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif
</div>
@endsection
