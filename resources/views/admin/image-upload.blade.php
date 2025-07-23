@extends('layout.admin')

@section('content')
<div class="container">
    <h1>Upload de Imagem do Header</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.image.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="header_image" class="form-label">Selecione a imagem</label>
            <input class="form-control" type="file" id="header_image" name="header_image" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar Imagem</button>
    </form>

    @isset($webpImage)
        <hr>
        <h3>Imagem atual:</h3>
        <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Imagem Header" style="max-height: 100px; display:block; margin-bottom:10px;">

        <form action="{{ route('admin.image.delete') }}" method="POST" style="margin-top: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Excluir Imagem</button>
        </form>
    @endisset
</div>
@endsection
