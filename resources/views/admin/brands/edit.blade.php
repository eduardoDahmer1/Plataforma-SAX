@extends('layout.admin')

@section('content')
<div class="container">
    <h1>Editar Marca</h1>

    <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nome da Marca</label>
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

        <div class="mb-3">
            <label for="image" class="form-label">Imagem da Marca</label>
            <input type="file" 
                   class="form-control @error('image') is-invalid @enderror" 
                   id="image" 
                   name="image" 
                   accept="image/*">
            @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if($brand->image)
                <img src="{{ asset('storage/' . $brand->image) }}" 
                     alt="Imagem da Marca" 
                     style="max-width:150px; margin-top:10px;">
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Cancelar</a>
        <a href="{{ route('admin.index') }}" class="btn btn-secondary">Admin</a>
    </form>
</div>
@endsection
