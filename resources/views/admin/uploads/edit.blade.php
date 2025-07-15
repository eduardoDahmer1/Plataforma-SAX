@extends('layout.admin')

@section('content')
<div class="container">
    <a href="{{ route('pages.home') }}" class="btn btn-primary mb-3">Home</a>
    <a href="{{ route('admin.uploads.index') }}" class="btn btn-primary mb-3">Adicionar novos arquivos</a>

    <h1>Editar Upload</h1>

    <form action="{{ route('admin.uploads.update', $upload->id) }}" method="POST" enctype="multipart/form-data" id="uploadForm">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="title">Título</label>
            <input type="text" name="title" class="form-control" value="{{ $upload->title }}">
        </div>
        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" class="form-control" rows="4">{{ $upload->description }}</textarea>
        </div>

        <div class="form-group">
            <label for="file">Arquivo (se desejar substituir)</label>
            <input type="file" name="file" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary mt-3">Salvar Alterações</button>
    </form>
</div>

<script>
    document.getElementById('uploadForm').addEventListener('submit', function() {
        var content = tinymce.get('description').getContent();
        document.querySelector('textarea[name="description"]').value = content;
    });
</script>
@endsection
