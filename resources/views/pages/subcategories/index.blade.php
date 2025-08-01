@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Subcategorias</h2>
    <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary mb-3">Nova Subcategoria</a>

    <!-- Total de Subcategorias exibidas -->
    <p class="text-muted">
        Exibindo {{ $subcategories->count() }} de {{ $subcategories->total() }} categoria(s).
    </p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Categoria Pai</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subcategories as $subcategory)
            <tr>
                <td>{{ $subcategory->name }}</td>
                <td>
                    {{ $subcategory->category ? ($subcategory->category->name ?: $subcategory->category->slug) : 'Sem Categoria' }}
                </td>

                <td>
                    <a href="{{ route('admin.subcategories.show', $subcategory->id) }}"
                        class="btn btn-sm btn-info">Ver</a>
                    <a href="{{ route('admin.subcategories.edit', $subcategory->id) }}"
                        class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('admin.subcategories.destroy', $subcategory->id) }}" method="POST"
                        style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger"
                            onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center">
        {{ $subcategories->links() }}
    </div>
</div>
@endsection