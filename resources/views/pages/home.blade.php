@extends('layout.layout')

@section('content')
<div class="container">
    <a href="{{ route('pages.contato') }}" class="btn btn-link">Contato</a>
    <a href="{{ route('pages.sobre') }}" class="btn btn-link">Sobre Nós</a>
    <a href="{{ route('pages.home') }}" class="btn btn-link">Home</a>

    <h2>Bem-vindo à Página Inicial</h2>
    <p>Esta é a página de uploads. Aqui você pode ver os arquivos que foram carregados.</p>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Exibir botão para todos os arquivos -->
    <a href="{{ route('uploads.index') }}" class="btn btn-primary mb-3">Ver Todos os Arquivos</a>

    <h4 class="mt-4">Arquivos Recentes:</h4>
    <ul>
        @foreach($uploads->take(5) as $upload) <!-- Exibe somente os 5 uploads mais recentes -->
        <li>
            <strong>{{ $upload->title ?? 'Sem título' }}</strong> <br>
            <p>{{ $upload->description ?? 'Sem descrição' }}</p>
            <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank">
                @if(in_array($upload->file_type, ['jpg', 'jpeg', 'png', 'gif']))
                <img src="{{ asset('storage/' . $upload->file_path) }}" alt="{{ $upload->title }}"
                     style="max-width: 100px;">
                @elseif(in_array($upload->file_type, ['mp4', 'avi', 'mov']))
                <video width="100" controls>
                    <source src="{{ asset('storage/' . $upload->file_path) }}" type="video/mp4">
                    Seu navegador não suporta o elemento de vídeo.
                </video>
                @else
                <span>Ver Arquivo</span>
                @endif
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endsection
