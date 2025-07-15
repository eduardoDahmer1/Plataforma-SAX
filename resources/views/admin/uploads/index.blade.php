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

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Botão para criar novo upload -->
    <a href="{{ route('admin.uploads.create') }}" class="btn btn-success mb-3">Novo Upload</a>

    <table class="table">
        <thead>
            <tr>
                <th>Título</th>
                <th>SKU</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($uploads as $upload)
            <tr>
                <td>{{ $upload->title }}</td>
                <td>
                    @if($upload->type === 'upload')
                        <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank">
                            {{ $upload->original_name }}
                        </a>
                    @elseif($upload->type === 'product')
                        {{ $upload->description }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($upload->type === 'upload')
                        <a href="{{ route('admin.uploads.edit', $upload->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('admin.uploads.destroy', $upload->id) }}" method="POST"
                            style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</button>
                        </form>
                    @else
                        <!-- Produtos não têm ações de edição aqui -->
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Paginação -->
    <div class="d-flex justify-content-center">
        {{ $uploads->links() }}
    </div>
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
