@extends('layout.admin')
@section('content')
<div class="container py-4">
    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <h1 class="mb-2 mb-md-0">Gerenciar Blogs</h1>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.blogs.create') }}" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Novo Blog
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="fa fa-folder me-1"></i> Gerenciar Categorias
            </button>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Categorias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm" class="mb-3">
                        @csrf
                        <input type="hidden" id="category_id">
                        <div class="input-group mb-2 flex-column flex-md-row gap-2">
                            <input type="text" id="category_name" class="form-control" placeholder="Nome da categoria">
                            <button type="submit" class="btn btn-success flex-shrink-0">
                                <i class="fa fa-save me-1"></i> Salvar
                            </button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped" id="categoryTable">
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
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead class="table-dark">
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
                    <td class="d-flex flex-column flex-md-row gap-2">
                        <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-warning btn-sm flex-fill">
                            <i class="fa fa-edit me-1"></i> Editar
                        </a>

                        <a href="{{ route('blogs.show', $blog->slug) }}" target="_blank"
                            class="btn btn-success btn-sm flex-fill">
                            <i class="fa fa-eye me-1"></i> Ver Pública
                        </a>

                        <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" class="flex-fill m-0"
                            onsubmit="return confirm('Excluir?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm w-100">
                                <i class="fa fa-trash me-1"></i> Excluir
                            </button>
                        </form>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadCategories);

function loadCategories() {
    fetch('/admin/blog-categories')
        .then(res => res.json())
        .then(data => {
            let rows = '';
            data.forEach(cat => {
                rows += `<tr>
                    <td>${cat.name}</td>
                    <td class="d-flex flex-column flex-md-row gap-2">
                        <button class="btn btn-warning btn-sm flex-fill" onclick="editCategory(${cat.id}, '${cat.name}')">
                            <i class="fa fa-edit me-1"></i> Editar
                        </button>
                        <button class="btn btn-danger btn-sm flex-fill" onclick="deleteCategory(${cat.id})">
                            <i class="fa fa-trash me-1"></i> Excluir
                        </button>
                    </td>
                </tr>`;
            });
            document.querySelector('#categoryTable tbody').innerHTML = rows;
        });
}

document.querySelector('#categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let id = document.querySelector('#category_id').value;
    let name = document.querySelector('#category_name').value;
    let url = '/admin/blog-categories' + (id ? '/' + id : '');
    let method = id ? 'PUT' : 'POST';

    fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                name: name
            })
        })
        .then(res => res.json())
        .then(() => {
            document.querySelector('#categoryForm').reset();
            document.querySelector('#category_id').value = '';
            loadCategories();
        });
});

function editCategory(id, name) {
    document.querySelector('#category_id').value = id;
    document.querySelector('#category_name').value = name;
}

function deleteCategory(id) {
    if (!confirm('Excluir categoria?')) return;
    fetch('/admin/blog-categories/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(() => loadCategories());
}
</script>
@endsection