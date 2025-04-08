@extends('layout.layout')

@section('content')
<div class="container">
    <a href="{{ route('pages.home') }}" class="btn btn-link">Home</a>

    <h2>Detalhes do Arquivo</h2>

    <div>
        <strong>Título:</strong> {{ $upload->title }} <br>
        <strong>Descrição:</strong> {{ $upload->description }} <br>

        <strong>Arquivo:</strong>
        @if(in_array($upload->file_type, ['jpg', 'jpeg', 'png', 'gif']))
            <div class="gallery">
                <a href="{{ asset('storage/' . $upload->file_path) }}" data-lightbox="image-1">
                    <img src="{{ asset('storage/' . $upload->file_path) }}" alt="{{ $upload->title }}" style="max-width: 100%; height: auto;">
                </a>
            </div>
        @elseif(in_array($upload->file_type, ['mp4', 'avi', 'mov']))
            <video width="100%" controls>
                <source src="{{ asset('storage/' . $upload->file_path) }}" type="video/mp4">
                Seu navegador não suporta o elemento de vídeo.
            </video>
        @elseif(in_array($upload->file_type, ['xls', 'xlsx']))
            <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="btn btn-sm btn-success">Baixar Arquivo Excel</a>
        @else
            <span>Ver Arquivo</span>
        @endif
    </div>

    <a href="{{ route('pages.home') }}" class="btn btn-primary mt-3">Voltar para a lista de arquivos</a>
</div>

<!-- Lightbox JS (Adicionando a funcionalidade de lightbox para imagens) -->
<script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css" rel="stylesheet">
@endsection
