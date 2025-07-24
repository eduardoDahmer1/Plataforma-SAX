@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Editar Subcategoria</h2>

    <form action="{{ route('admin.subcategories.update', $subcategory->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Campo Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nome da Subcategoria</label>
            <input
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="name"
                name="name"
                value="{{ old('name', $subcategory->name) }}"
                required
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Campo Categoria Pai --}}
        <div class="mb-3">
            <label for="category_id" class="form-label">Categoria Pai</label>
            <select
                class="form-select @error('category_id') is-invalid @enderror"
                id="category_id"
                name="category_id"
                required
            >
                <option value="">Selecione uma categoria</option>
                @foreach ($categories as $category)
                <option
                        value="{{ $category->id }}"
                        {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}
                    >
                        {{ $category->name ?: $category->slug }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Botões --}}
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
