@extends('layout.admin')

@section('content')

@if(session('success'))
    <p style="color:green;">{{ session('success') }}</p>
@endif

@if(session('error'))
    <p style="color:red;">{{ session('error') }}</p>
@endif

@if($errors->any())
    <ul style="color:red;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<!-- Formulário de upload -->
<form action="{{ route('admin.image.upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="image" required>
    <button type="submit">Enviar Imagem</button>
</form>

<!-- Exibe a imagem convertida e permite exclusão -->
@if(session('webpImage'))
    <div>
        <img src="{{ asset('storage/uploads/' . session('webpImage')) }}" alt="Imagem convertida" style="height: 5em;">
        <form action="{{ route('admin.image.delete') }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" style="color: red;">Excluir Imagem</button>
        </form>
    </div>
@endif

@endsection
