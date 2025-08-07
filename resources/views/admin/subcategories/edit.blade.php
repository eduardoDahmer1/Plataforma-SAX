@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Editar Subcategoria</h2>

    {{-- FORM PRINCIPAL --}}
    <form action="{{ route('admin.subcategories.update', $subcategory->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nome da Subcategoria</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                value="{{ old('name', $subcategory->name) }}" required>
            @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Categoria Pai --}}
        <div class="mb-3">
            <label for="category_id" class="form-label">Categoria Pai</label>
            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                <option value="">Selecione uma categoria</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name ?: $category->slug }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Foto --}}
        <div class="mb-3">
            <label class="form-label">Foto</label>
            @if ($subcategory->photo)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $subcategory->photo) }}" alt="Foto" width="100">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('delete-photo-form').submit();" class="btn btn-sm btn-danger">Excluir Foto</a>
                </div>
            @endif
            <input type="file" name="photo" class="form-control mt-2">
        </div>

        {{-- Banner --}}
        <div class="mb-3">
            <label class="form-label">Banner</label>
            @if ($subcategory->banner)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $subcategory->banner) }}" alt="Banner" width="100">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('delete-banner-form').submit();" class="btn btn-sm btn-danger">Excluir Banner</a>
                </div>
            @endif
            <input type="file" name="banner" class="form-control mt-2">
        </div>

        {{-- Botões --}}
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>

    {{-- FORM DELETE FOTO (fora do form principal) --}}
    @if ($subcategory->photo)
    <form id="delete-photo-form" action="{{ route('admin.subcategories.deletePhoto', $subcategory->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

    {{-- FORM DELETE BANNER (fora do form principal) --}}
    @if ($subcategory->banner)
    <form id="delete-banner-form" action="{{ route('admin.subcategories.deleteBanner', $subcategory->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

</div>
@endsection
