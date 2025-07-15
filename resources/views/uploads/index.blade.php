@extends('layout.admin')

<style>
.alert .close {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 1.5rem;
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
}
</style>

@section('content')
<div class="container">
    <a href="{{ route('admin.index') }}" class="btn btn-primary mb-3">Admin</a>
    <a href="{{ route('pages.home') }}" class="btn btn-primary mb-3">Home</a>

    <h1>Lista de Arquivos</h1>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Botão para criar novo upload -->
    <a href="{{ route('uploads.create') }}" class="btn btn-success mb-3">Novo Upload</a>

    <table class="table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Arquivo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($uploads as $upload)
            <tr>
                <td>{{ $upload->title }}</td>
                <td><a href="{{ asset('storage/' . $upload->file_path) }}"
                        target="_blank">{{ $upload->original_name }}</a></td>
                <td>
                    <a href="{{ route('uploads.edit', $upload->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('uploads.destroy', $upload->id) }}" method="POST"
                        style="display:inline-block">
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    var closeButton = document.querySelector('.alert .close');

    if (closeButton) {
        closeButton.addEventListener('click', function() {
            var alertMessage = this.closest('.alert');
            alertMessage.style.display = 'none'; // Esconde a mensagem
        });
    }
});
</script>