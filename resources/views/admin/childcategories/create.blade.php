@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Criar Sub-Subcategoria</h2>
    <form action="{{ route('admin.childcategories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Nome --}}
        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        {{-- Subcategoria Pai --}}
        <div class="form-group">
            <label>Subcategoria Pai</label>
            <select name="subcategory_id" class="form-control" required>
                @foreach ($subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Photo --}}
        <div class="form-group">
            <label>Foto (photo)</label>
            <input type="file" name="photo" class="form-control-file" accept="image/*">
        </div>

        {{-- Banner --}}
        <div class="form-group">
            <label>Banner</label>
            <input type="file" name="banner" class="form-control-file" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success">Salvar</button>
    </form>
</div>
@endsection
