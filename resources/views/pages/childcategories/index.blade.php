@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Sub-Subcategorias</h2>
    <a href="{{ route('admin.childcategories.create') }}" class="btn btn-primary mb-3">Nova Sub-Subcategoria</a>

    <table class="table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Subcategoria Pai</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($childcategories as $childcategory)
                <tr>
                    <td>{{ $childcategory->name }}</td>
                    <td>{{ $childcategory->subcategory->name ?? 'Sem Subcategoria' }}</td>
                    <td>
                    <a href="{{ route('admin.childcategories.show', $childcategory->id) }}"
                    class="btn btn-sm btn-info">Ver</a>
                        <a href="{{ route('admin.childcategories.edit', $childcategory->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('admin.childcategories.destroy', $childcategory->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
