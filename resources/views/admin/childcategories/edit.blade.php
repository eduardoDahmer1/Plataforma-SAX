@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Editar Childcategory</h2>

    <form action="{{ route('admin.childcategories.update', $childcategory->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Campo Nome --}}
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

        {{-- Campo Subcategoria Pai --}}
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

        {{-- Botões --}}
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('admin.childcategories.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
