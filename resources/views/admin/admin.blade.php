@extends('layout.admin')

@section('content')
<div class="container">

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

    <div class="row">
        <!-- Formulário de upload -->
        <div class="col-md-6">
            <form action="{{ route('admin.image.upload') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                @csrf
                <div class="mb-3">
                    <label for="header_image" class="form-label">Selecione a imagem do header</label>
                    <input class="form-control" type="file" id="header_image" name="header_image" required>
                </div>
                <button type="submit" class="btn btn-primary">Enviar Imagem</button>
            </form>

            @if ($webpImage)
            <div class="mb-3">
                <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Imagem Header"
                    class="img-fluid mb-2" style="max-height: 100px;">
                <form action="{{ route('admin.image.delete') }}" method="POST"
                    onsubmit="return confirm('Tem certeza que deseja excluir a imagem?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir Imagem</button>
                </form>
            </div>
            @endif
        </div>

        <!-- Formulário para imagem noimage -->
        <div class="col-md-6">
            <form action="{{ route('admin.noimage.upload') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                @csrf
                <div class="mb-3">
                    <label for="noimage" class="form-label">Selecione a imagem noimage</label>
                    <input class="form-control" type="file" id="noimage" name="noimage" required>
                </div>
                <button type="submit" class="btn btn-secondary">Enviar Noimage</button>
            </form>

            @if ($noimage)
            <div class="mb-3">
                <img src="{{ asset('storage/uploads/' . $noimage) }}" alt="Imagem noimage"
                    class="img-fluid mb-2" style="max-height: 100px;">
                <form action="{{ route('admin.noimage.delete') }}" method="POST"
                    onsubmit="return confirm('Tem certeza que deseja excluir a imagem?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir Noimage</button>
                </form>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
