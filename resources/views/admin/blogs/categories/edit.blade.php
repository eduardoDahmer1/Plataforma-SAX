@extends('layout.admin')
@section('content')
<div class="container py-4">
    <h1 class="mb-4">Editar Categoria</h1>

    <form action="{{ route('admin.blog-categories.update', $category) }}" method="POST" enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nome da Categoria</label>
            <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $category->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="banner" class="form-label">Banner</label>
            <input type="file" name="banner" class="form-control" id="banner" accept="image/*">
            @if($category->banner)
                <img src="{{ asset('storage/' . $category->banner) }}" class="img-fluid mt-2 rounded" style="max-height:200px;">
            @endif
        </div>

        <button type="submit" class="btn btn-success"><i class="fa fa-save me-1"></i> Salvar Alterações</button>
        <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>
@endsection
