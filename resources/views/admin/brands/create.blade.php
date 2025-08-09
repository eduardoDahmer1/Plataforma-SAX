@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2>Adicionar Nova Marca</h2>

    <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nome da Marca</label>
            <input type="text" 
                   class="form-control @error('name') is-invalid @enderror" 
                   id="name" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" 
                   class="form-control @error('slug') is-invalid @enderror" 
                   id="slug" 
                   name="slug" 
                   value="{{ old('slug') }}" 
                   required>
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Logo da Marca</label>
            <input type="file" 
                   class="form-control @error('image') is-invalid @enderror" 
                   id="image" 
                   name="image" 
                   accept="image/*">
            @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="banner" class="form-label">Banner</label>
            <input type="file" 
                   class="form-control @error('banner') is-invalid @enderror" 
                   id="banner" 
                   name="banner" 
                   accept="image/*">
            @error('banner')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Cancelar</a>
        <a href="{{ route('admin.index') }}" class="btn btn-secondary">Admin</a>
    </form>
</div>
@endsection
