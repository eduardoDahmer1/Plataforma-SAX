@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Detalhes do Cliente</h2>

    <table class="table table-bordered mt-3">
        <tr><th>ID</th><td>{{ $client->id }}</td></tr>
        <tr><th>Nome</th><td>{{ $client->name }}</td></tr>
        <tr><th>Email</th><td>{{ $client->email }}</td></tr>
        <tr><th>Data de Cadastro</th><td>{{ $client->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><th>Tipo</th>
            <td>
                @if($client->user_type == 1) Cliente
                @elseif($client->user_type == 2) Admin
                @elseif($client->user_type == 3) Curso
                @else Desconhecido
                @endif
            </td>
        </tr>
    </table>

    <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
