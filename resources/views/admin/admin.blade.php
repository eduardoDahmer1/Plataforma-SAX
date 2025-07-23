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
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulário de upload -->
    <form action="{{ route('admin.image.upload') }}" method="POST" enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="header_image" class="form-label">Selecione a imagem</label>
            <input class="form-control" type="file" id="header_image" name="header_image" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar Imagem</button>
    </form>

    @if ($webpImage)
    <div class="mb-3">
        <h5>Imagem atual:</h5>
        <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Imagem Header" style="max-height: 100px; display: block; margin-bottom: 10px;">
        <form action="{{ route('admin.image.delete') }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir a imagem?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Excluir Imagem</button>
        </form>
    </div>
@endif

</div>
@endsection
