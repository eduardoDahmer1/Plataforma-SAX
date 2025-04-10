@extends('layout.layout')

@section('content')
<div class="container mt-4">
    <h2>Página Administrativa</h2>
    <p>Bem-vindo ao painel de administração.</p>

    <!-- Quadro com links laterais -->
    <div class="row mt-4">
        <div class="col-md-3">
            <!-- Links laterais -->
            <div class="list-group">
                <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action">Usuários</a>
                <!-- Adicione outros links conforme necessário -->
                <a href="{{ route('uploads.index') }}" class="list-group-item list-group-item-action">Adicionar novos arquivos</a>
                <a href="{{ route('pages.home') }}" class="list-group-item list-group-item-action">Home</a>
            </div>
        </div>
        <div class="col-md-9">
            <!-- Conteúdo principal que você vai ver depois -->
            <div class="card">
                <div class="card-header">
                    <strong>Quadro Principal</strong>
                </div>
                <div class="card-body">
                    <p>Aqui você pode adicionar o conteúdo principal que será visualizado depois.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

