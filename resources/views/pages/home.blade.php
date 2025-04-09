@extends('layout.layout')

@section('content')
<div class="container">
    @if (auth()->check()) <!-- Verifica se o usuário está logado -->
        <a href="{{ route('pages.home') }}" class="btn btn-link">Home</a>

        <h2>Bem-vindo à Página Inicial</h2>
        <p>Esta é a página de uploads. Aqui você pode ver os arquivos que foram carregados.</p>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Exibir botão para todos os arquivos -->
        <a href="{{ route('uploads.index') }}" class="btn btn-primary mb-3">Ver Todos os Arquivos</a>

        <h4 class="mt-4">Arquivos Recentes:</h4>
        <ul class="list-group">
            @foreach($uploads->take(5) as $upload) <!-- Exibe somente os 5 uploads mais recentes -->
            <li class="list-group-item mb-3">
                <strong>{{ $upload->title ?? 'Sem título' }}</strong> <br>
                <p class="text-home">{{ $upload->description ?? 'Sem descrição' }}</p>

                <!-- Exibir conteúdo -->
                @if($upload->file)
                    <div class="mb-2">
                        @php
                            $fileExtension = pathinfo($upload->file, PATHINFO_EXTENSION);
                        @endphp

                        @if(in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                            <img src="{{ asset('storage/' . $upload->file) }}" alt="Imagem" class="img-fluid rounded" style="max-height: 300px;">
                        @elseif(strtolower($fileExtension) === 'pdf')
                            <a href="{{ asset('storage/' . $upload->file) }}" target="_blank" class="btn btn-sm btn-secondary">Abrir PDF</a>
                        @else
                            <a href="{{ asset('storage/' . $upload->file) }}" target="_blank" class="btn btn-sm btn-secondary">Baixar Arquivo</a>
                        @endif
                    </div>
                @else
                    <p><em>Nenhum arquivo associado.</em></p>
                @endif

                <a href="{{ route('uploads.show', $upload->id) }}" class="btn btn-sm btn-info">Ver Detalhes</a>
            </li>
            @endforeach
        </ul>
    @else <!-- Caso o usuário não esteja logado -->
        <div class="alert alert-warning">
            Você precisa estar logado para acessar esta página. <a href="{{ route('login') }}">Clique aqui para fazer login</a>.
        </div>
    @endif
</div>
@endsection
