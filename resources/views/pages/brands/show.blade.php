@extends('layout.admin')

@section('content')
<div class="container">
    <h1>Detalhes da Marca</h1>

    <p><strong>ID:</strong> {{ $brand->id }}</p>
    <p><strong>Nome:</strong> {{ $brand->name }}</p>

    <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-warning">Editar</a>
    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">Voltar</a>
    <a href="{{ route('admin.index') }}" class="btn btn-secondary">Admin</a>
</div>
@endsection
