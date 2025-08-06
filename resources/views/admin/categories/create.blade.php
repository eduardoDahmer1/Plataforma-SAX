@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2>Criar Nova Categoria</h2>

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" class="form-control" name="slug" required>
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Foto (Logo)</label>
            <input type="file" class="form-control" name="photo" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="banner" class="form-label">Banner</label>
            <input type="file" class="form-control" name="banner" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Voltar</a>
        <a href="{{ route('admin.index') }}" class="btn btn-secondary">Admin</a>
    </form>
</div>
@endsection
