@extends('layout.admin')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-envelope me-2"></i>Mensagens</h2>

    {{-- Filtros --}}
    <div class="mb-3">
        <div class="btn-group flex-wrap" role="group" aria-label="Filtros de contato">
            <a href="{{ route('admin.contatos.index') }}"
               class="btn {{ request('type') === null ? 'btn-dark' : 'btn-outline-dark' }}">
               <i class="fas fa-list me-1"></i>Todos
            </a>
            <a href="{{ route('admin.contatos.index', ['type' => 1]) }}"
               class="btn {{ request('type') == 1 ? 'btn-primary' : 'btn-outline-primary' }}">
               <i class="fas fa-comment me-1"></i>Fale Conosco
            </a>
            <a href="{{ route('admin.contatos.index', ['type' => 2]) }}"
               class="btn {{ request('type') == 2 ? 'btn-success' : 'btn-outline-success' }}">
               <i class="fas fa-file-alt me-1"></i>Currículos
            </a>
            <a href="{{ route('admin.contacts.export', ['type' => request('type')]) }}"
               class="btn btn-outline-secondary">
               <i class="fas fa-download me-1"></i>Baixar Contatos
            </a>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="table-responsive shadow-sm rounded-3 border">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tipo</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Mensagem</th>
                    <th>Anexo</th>
                    <th>Data</th>
                    <th class="text-center">Ações</th>
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
                    <td>{{ Str::limit($contact->message, 50) }}</td>
                    <td>
                        @if($contact->attachment)
                            <a href="{{ asset('storage/' . $contact->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file me-1"></i>Ver arquivo
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $contact->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        <form action="{{ route('admin.contatos.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Confirma exclusão?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash-alt me-1"></i>Excluir
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-3">Nenhuma mensagem encontrada.</td>
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
