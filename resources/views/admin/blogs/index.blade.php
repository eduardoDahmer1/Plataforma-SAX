@extends('layout.admin')
@section('content')
<div class="container py-4">
    <h1>Gerenciar Blogs</h1>
    <a href="{{ route('admin.blogs.create') }}" class="btn btn-success mb-3">Novo Blog</a>
    <a href="#" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#categoryModal">Gerenciar
        Categorias</a>

    <!-- Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Categorias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm">
                        @csrf
                        <input type="hidden" id="category_id">
                        <div class="mb-3">
                            <label>Nome</label>
                            <input type="text" id="category_name" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-success">Salvar</button>
                    </form>
                    <hr>
                    <table class="table" id="categoryTable">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                    <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Excluir?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Excluir</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


<script>
document.addEventListener('DOMContentLoaded', loadCategories);

function loadCategories(){
    fetch('/admin/blog-categories')
        .then(res => res.json())
        .then(data => {
            let rows = '';
            data.forEach(cat => {
                rows += `<tr>
                    <td>${cat.name}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editCategory(${cat.id}, '${cat.name}')">Editar</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteCategory(${cat.id})">Excluir</button>
                    </td>
                </tr>`;
            });
            document.querySelector('#categoryTable tbody').innerHTML = rows;
        });
}

document.querySelector('#categoryForm').addEventListener('submit', function(e){
    e.preventDefault();
    let id = document.querySelector('#category_id').value;
    let name = document.querySelector('#category_name').value;
    let url = '/admin/blog-categories' + (id ? '/' + id : '');
    let method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {'Content-Type': 'application/json','X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({name: name})
    })
    .then(res => res.json())
    .then(() => {
        document.querySelector('#categoryForm').reset();
        document.querySelector('#category_id').value = '';
        loadCategories();
    });
});

function editCategory(id, name){
    document.querySelector('#category_id').value = id;
    document.querySelector('#category_name').value = name;
}

function deleteCategory(id){
    if(!confirm('Excluir categoria?')) return;
    fetch('/admin/blog-categories/' + id, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
    })
    .then(() => loadCategories());
}
</script>

@endsection
