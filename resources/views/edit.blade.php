@extends('layout.layout')

@section('content')
<div class="container">
    <a href="{{ route('pages.contato') }}" class="btn btn-link">Contato</a>
    <a href="{{ route('pages.sobre') }}" class="btn btn-link">Sobre Nós</a>
    <a href="{{ route('pages.home') }}" class="btn btn-link">Home</a>
    
    <h1>Editar Upload</h1>

    <form action="{{ route('uploads.update', $upload->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="title">Título:</label>
            <input type="text" name="title" class="form-control" value="{{ $upload->title }}" required>
        </div>

        <div class="form-group">
            <label for="description">Descrição:</label>
            <input type="text" name="description" class="form-control" value="{{ $upload->description }}">
        </div>

        <div class="form-group">
            <label>Arquivo atual:</label><br>
            <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank">{{ $upload->original_name }}</a>
        </div>

        <div class="form-group">
            <label for="file">Novo Arquivo (opcional):</label>
            <input type="file" name="file" class="form-control-file">
        </div>

        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>
@endsection
