@extends('layout.admin')
@section('content')
<div class="container py-4">
    <h1>Gerenciar Blogs</h1>
    <a href="{{ route('admin.blogs.create') }}" class="btn btn-success mb-3">Novo Blog</a>
    <table class="table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Publicado</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($blogs as $blog)
            <tr>
                <td>{{ $blog->title }}</td>
                <td>{{ $blog->published_at ? $blog->published_at->format('d/m/Y') : '-' }}</td>
                <td>
                    <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
