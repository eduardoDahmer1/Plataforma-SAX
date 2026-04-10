@extends('layout.admin')
@section('content')
<x-admin.card>
    <x-admin.page-header title="Editar Categoria de Blog" description="Atualize o nome e o banner da categoria">
        <x-slot:actions>
            <a href="{{ route('admin.blog-categories.index') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
            </a>
        </x-slot:actions>
    </x-admin.page-header>

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

        <x-admin.form-actions :cancelRoute="route('admin.blog-categories.index')" submitLabel="Salvar Alterações" />
    </form>
</x-admin.card>
@endsection
