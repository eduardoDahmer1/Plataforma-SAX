@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Criar Subcategoria</h2>
    <form action="{{ route('admin.subcategories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group mb-3">
            <label>Nome</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Categoria Pai</label>
            <select name="category_id" class="form-control" required>
                <option value="">Selecione uma categoria</option>
                @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name ?: $category->slug }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Foto</label>
            <input type="file" name="photo" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label>Banner</label>
            <input type="file" name="banner" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
    </form>
</div>
@endsection
