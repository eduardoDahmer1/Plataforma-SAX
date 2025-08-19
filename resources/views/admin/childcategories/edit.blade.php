@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-folder me-2"></i>Editar Sub-Subcategoria</h2>

    <form action="{{ route('admin.childcategories.update', $childcategory->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-tag me-1"></i>Nome</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $childcategory->name) }}" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Subcategoria Pai --}}
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-sitemap me-1"></i>Subcategoria Pai</label>
            <select name="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror" required>
                <option value="">Selecione uma subcategoria</option>
                @foreach ($subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $childcategory->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                        {{ $subcategory->name }}
                    </option>
                @endforeach
            </select>
            @error('subcategory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Foto --}}
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-image me-1"></i>Foto</label>
            @if ($childcategory->photo)
                <div class="position-relative d-inline-block mb-2">
                    <img src="{{ asset('storage/' . $childcategory->photo) }}" class="img-thumbnail" style="max-width:150px;">
                    <button onclick="event.preventDefault(); document.getElementById('delete-photo-form').submit();" class="btn btn-sm btn-danger position-absolute top-0 end-0"><i class="fas fa-times"></i></button>
                </div>
            @endif
            <input type="file" name="photo" class="form-control mt-2">
        </div>

        {{-- Banner --}}
        <div class="mb-3">
            <label class="form-label"><i class="fas fa-images me-1"></i>Banner</label>
            @if ($childcategory->banner)
                <div class="position-relative d-inline-block mb-2">
                    <img src="{{ asset('storage/' . $childcategory->banner) }}" class="img-thumbnail" style="max-width:150px;">
                    <button onclick="event.preventDefault(); document.getElementById('delete-banner-form').submit();" class="btn btn-sm btn-danger position-absolute top-0 end-0"><i class="fas fa-times"></i></button>
                </div>
            @endif
            <input type="file" name="banner" class="form-control mt-2">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
            <a href="{{ route('admin.childcategories.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Cancelar</a>
        </div>
    </form>

    @if ($childcategory->photo)
    <form id="delete-photo-form" action="{{ route('admin.childcategories.deletePhoto', $childcategory->id) }}" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

    @if ($childcategory->banner)
    <form id="delete-banner-form" action="{{ route('admin.childcategories.deleteBanner', $childcategory->id) }}" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
    @endif
</div>
@endsection
