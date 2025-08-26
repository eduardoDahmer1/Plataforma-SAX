@extends('layout.admin')

@section('content')
<div class="container py-4">

    {{-- Alertas --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        {{-- Card Header Image --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Imagem do Header</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.image.upload') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <input class="form-control" type="file" id="header_image" name="header_image" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Enviar Imagem</button>
                    </form>

                    @if ($webpImage)
                    <div class="text-center">
                        <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Imagem Header"
                            class="img-fluid mb-2 rounded shadow-sm" style="max-height: 120px;">
                        <form action="{{ route('admin.image.delete') }}" method="POST"
                            onsubmit="return confirm('Tem certeza que deseja excluir a imagem?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">Excluir Imagem</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card Noimage --}}
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Imagem Noimage</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.noimage.upload') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <input class="form-control" type="file" id="noimage" name="noimage" required>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100">Enviar Noimage</button>
                    </form>

                    @if ($noimage)
                    <div class="text-center">
                        <img src="{{ asset('storage/uploads/' . $noimage) }}" alt="Imagem Noimage"
                            class="img-fluid mb-2 rounded shadow-sm" style="max-height: 120px;">
                        <form action="{{ route('admin.noimage.delete') }}" method="POST"
                            onsubmit="return confirm('Tem certeza que deseja excluir a imagem?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">Excluir Noimage</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
