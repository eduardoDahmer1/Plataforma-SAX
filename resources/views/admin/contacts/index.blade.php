@extends('layout.admin')

@section('content')
<div class="container py-4">
    <h2>Mensagens</h2>

    <div class="mb-3">
        <div class="btn-group flex-wrap" role="group" aria-label="Filtros de contato">
            <a href="{{ route('admin.contatos.index') }}"
                class="btn {{ request('type') === null ? 'btn-dark' : 'btn-outline-dark' }}">
                Todos
            </a>
            <a href="{{ route('admin.contatos.index', ['type' => 1]) }}"
                class="btn {{ request('type') == 1 ? 'btn-primary' : 'btn-outline-primary' }}">
                Fale Conosco
            </a>
            <a href="{{ route('admin.contatos.index', ['type' => 2]) }}"
                class="btn {{ request('type') == 2 ? 'btn-success' : 'btn-outline-success' }}">
                Currículos
            </a>
            <a href="{{ route('admin.contatos.export', ['type' => request('type')]) }}"
                class="btn {{ request('type') === null ? 'btn-dark' : 'btn-outline-dark' }}">
                Baixar Contatos
            </a>
        </div>

    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tipo</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Mensagem</th>
                    <th>Anexo</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                <tr>
                    <td>
                        @switch($contact->contact_type)
                        @case(1) Fale Conosco @break
                        @case(2) Currículo @break
                        @default Desconhecido
                        @endswitch
                    </td>
                    <td>{{ $contact->name }}</td>
                    <td>{{ $contact->email }}</td>
                    <td>{{ $contact->phone }}</td>
                    <td>{{ $contact->message }}</td>
                    <td>
                        @if($contact->attachment)
                        <a href="{{ asset('storage/' . $contact->attachment) }}" target="_blank">Ver arquivo</a>
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ $contact->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST"
                            onsubmit="return confirm('Confirma exclusão?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">Nenhuma mensagem encontrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação --}}
    <div class="mt-3">
        {{ $contacts->appends(['type' => request('type')])->links() }}
    </div>
</div>
@endsection