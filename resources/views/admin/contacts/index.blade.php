@extends('layout.admin')

@section('content')
<div class="container py-4">
    
    <h2>Mensagens de Contato</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Mensagem</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $contact)
                <tr>
                    <td>{{ $contact->name }}</td>
                    <td>{{ $contact->email }}</td>
                    <td>{{ $contact->phone }}</td>
                    <td>{{ $contact->message }}</td>
                    <td>{{ $contact->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Nenhuma mensagem encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $contacts->links() }}
</div>
@endsection
