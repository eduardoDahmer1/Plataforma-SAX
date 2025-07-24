@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Criar Sub-Subcategoria</h2>
    <form action="{{ route('admin.childcategories.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Subcategoria Pai</label>
            <select name="subcategory_id" class="form-control">
                @foreach ($subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Salvar</button>
    </form>
</div>
@endsection

