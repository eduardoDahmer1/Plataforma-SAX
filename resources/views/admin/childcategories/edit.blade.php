@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Editar Childcategory</h2>

    {{-- FORM PRINCIPAL --}}
    <form action="{{ route('admin.childcategories.update', $childcategory->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nome da Childcategory</label>
            <input
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="name"
                name="name"
                value="{{ old('name', $childcategory->name) }}"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Subcategoria Pai --}}
        <div class="mb-3">
            <label for="subcategory_id" class="form-label">Subcategoria Pai</label>
            <select
                class="form-select @error('subcategory_id') is-invalid @enderror"
                id="subcategory_id"
                name="subcategory_id"
                required
            >
                <option value="">Selecione uma subcategoria</option>
                @foreach ($subcategories as $subcategory)
                    <option
                        value="{{ $subcategory->id }}"
                        {{ old('subcategory_id', $childcategory->subcategory_id) == $subcategory->id ? 'selected' : '' }}
                    >
                        {{ $subcategory->name }}
                    </option>
                @endforeach
            </select>
            @error('subcategory_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Foto --}}
        <div class="mb-3">
            <label class="form-label">Foto</label>
            @if ($childcategory->photo)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $childcategory->photo) }}" alt="Foto" width="100">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('delete-photo-form').submit();" class="btn btn-sm btn-danger">Excluir Foto</a>
                </div>
            @endif
            <input type="file" name="photo" class="form-control mt-2" accept="image/*">
        </div>

        {{-- Banner --}}
        <div class="mb-3">
            <label class="form-label">Banner</label>
            @if ($childcategory->banner)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $childcategory->banner) }}" alt="Banner" width="100">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('delete-banner-form').submit();" class="btn btn-sm btn-danger">Excluir Banner</a>
                </div>
            @endif
            <input type="file" name="banner" class="form-control mt-2" accept="image/*">
        </div>

        {{-- Botões --}}
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('admin.childcategories.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>

    {{-- FORM DELETE FOTO (fora do form principal) --}}
    @if ($childcategory->photo)
    <form id="delete-photo-form" action="{{ route('admin.childcategories.deletePhoto', $childcategory->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

    {{-- FORM DELETE BANNER (fora do form principal) --}}
    @if ($childcategory->banner)
    <form id="delete-banner-form" action="{{ route('admin.childcategories.deleteBanner', $childcategory->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif

</div>
@endsection
