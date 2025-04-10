@extends('layout.layout')

@section('content')
<div class="container">
    <a href="{{ route('pages.home') }}" class="btn btn-primary mb-3">Home</a>

    <h2>Detalhes do Arquivo</h2>

    <div class="mb-3">
        <strong>Título:</strong> {{ $upload->title }} <br>
        <strong>Descrição:</strong> {!! $upload->description !!}<br>
    </div>

    <strong>Arquivo:</strong>
    <div class="mt-3">
        @php
        $fileType = strtolower($upload->file_type);
        $filePath = asset('storage/' . $upload->file_path);
        @endphp

        @if(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
        <div class="gallery">
            <a href="{{ $filePath }}" data-lightbox="image-1">
                <img src="{{ $filePath }}" alt="{{ $upload->title }}" style="max-width: 100%; height: auto;">
            </a>
        </div>

        @elseif(in_array($fileType, ['mp4', 'avi', 'mov']))
        <video width="100%" height="auto" controls>
            <source src="{{ $filePath }}" type="video/{{ $fileType }}">
            Seu navegador não suporta o elemento de vídeo.
        </video>

        @elseif(in_array($fileType, ['pdf']))
        <iframe src="{{ $filePath }}" width="100%" height="600px" style="border: none;"></iframe>

        @elseif(in_array($fileType, ['xls', 'xlsx', 'csv']))
        <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-success">Baixar Arquivo Excel</a>

        @elseif(in_array($fileType, ['doc', 'docx', 'txt']))
        <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-secondary">Ver Documento</a>

        @else
        <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-dark">Baixar Arquivo</a>
        @endif
    </div>

    {{-- Verifica se o usuário é admin master --}}
    @if(auth()->user()->user_type == 1)
    <a href="{{ route('uploads.index') }}" class="btn btn-primary mt-4">Voltar para a lista de arquivos</a>
    @endif
</div>

@endsection