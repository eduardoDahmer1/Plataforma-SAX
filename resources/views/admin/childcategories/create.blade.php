@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-plus me-2"></i>Criar Sub-Subcategoria</h2>

    <form action="{{ route('admin.childcategories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label"><i class="fas fa-tag me-1"></i>Nome</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><i class="fas fa-sitemap me-1"></i>Subcategoria Pai</label>
            <select name="subcategory_id" class="form-select" required>
                @foreach ($subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label"><i class="fas fa-image me-1"></i>Foto</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
        </div>

        <div class="mb-3">
            <label class="form-label"><i class="fas fa-images me-1"></i>Banner</label>
            <input type="file" name="banner" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Salvar</button>
    </form>
</div>
@endsection
