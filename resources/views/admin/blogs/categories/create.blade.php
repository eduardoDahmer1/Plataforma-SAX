@extends('layout.admin')
@section('content')
<div class="container py-4">
    <h1 class="mb-4">Criar Categoria</h1>

    <form action="{{ route('admin.blog-categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nome da Categoria</label>
            <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label for="banner" class="form-label">Banner</label>
            <input type="file" name="banner" class="form-control" id="banner" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success"><i class="fa fa-plus me-1"></i> Criar Categoria</button>
        <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>
@endsection
