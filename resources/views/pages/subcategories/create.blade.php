@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Criar Subcategoria</h2>
    <form action="{{ route('admin.subcategories.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label>Categoria Pai</label>
            <select name="category_id" class="form-control">
                @foreach ($categories as $category)
                <option
                    value="{{ $category->id }}"
                    {{ old('category_id') == $category->id ? 'selected' : '' }}
                >
                    {{ $category->name ?: $category->slug }}
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Salvar</button>
    </form>
</div>
@endsection
