@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.centro_mensagens_titulo') }}"
        description="{{ __('messages.gestao_comunicacoes_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.contacts.export', ['type' => request('type')]) }}"
               class="btn btn-outline-dark btn-sm rounded-0 px-3">
               <i class="fas fa-download me-2"></i> {{ __('messages.exportar_csv_btn') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.contatos.index') }}" id="filterForm" class="row g-3 mb-4 align-items-end">
        <input type="hidden" name="type" value="{{ $type }}">
        
        <div class="col-md-auto">
            <div class="d-flex gap-2 pb-2 border-bottom">
                <a href="{{ route('admin.contatos.index') }}" class="sax-filter-link {{ !$type ? 'active' : '' }}">Todos</a>
                <a href="{{ route('admin.contatos.index', ['type' => 1]) }}" class="sax-filter-link {{ $type == 1 ? 'active' : '' }}">Consultas</a>
                <a href="{{ route('admin.contatos.index', ['type' => 2]) }}" class="sax-filter-link {{ $type == 2 ? 'active' : '' }}">Currículos</a>
                <a href="{{ route('admin.contatos.index', ['type' => 3]) }}" class="sax-filter-link {{ $type == 3 ? 'active' : '' }}">Newsletter</a>
            </div>
        </div>

        <div class="col-md-2 ms-auto">
            <label class="small fw-bold">Exibir</label>
            <select name="per_page" class="form-select" onchange="this.form.submit()">
                @foreach([20, 30, 50, 100] as $opt)
                    <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="row g-3">
        @forelse($contacts as $contact)
            <div class="col-12">
                <div class="card border rounded-0 shadow-none sax-message-card">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="sax-dot {{ $contact->contact_type == 2 ? 'bg-success' : 'bg-primary' }} me-2"></div>
                                    <span class="fw-bold text-dark text-uppercase small">{{ $contact->name }}</span>
                                </div>
                                <div class="x-small text-muted mt-1 ps-3">
                                    {{ $contact->email }}<br>{{ $contact->phone }}
                                </div>
                            </div>
                            <div class="col-md-5 py-3 py-md-0 border-start-md ps-md-4">
                                <p class="small text-secondary mb-0">"{{ Str::limit($contact->message, 120) }}"</p>
                            </div>
                            <div class="col-md-2 text-md-center">
                                <span class="d-block x-small fw-bold text-dark">{{ $contact->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="col-md-2 text-md-end">
                                @if($contact->attachment)
                                    <a href="{{ asset('storage/' . $contact->attachment) }}" target="_blank" class="btn btn-sm btn-link text-dark">Anexo</a>
                                @endif
                                <form action="{{ route('admin.contatos.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Confirmar exclusão?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" 
                                            onclick="deleteContact({{ $contact->id }}, this)" 
                                            class="btn btn-sm btn-link text-danger delete-btn">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5"><p class="text-muted">Nenhuma mensagem encontrada.</p></div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $contacts->links() }}
    </div>
</x-admin.card>
@endsection
<script>
function deleteContact(id, btn) {
    if (!confirm('Confirmar exclusão?')) return;

    fetch(`/admin/contatos/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove o card da tela sem recarregar
            btn.closest('.card').parentElement.remove();
        } else {
            alert('Erro ao excluir.');
        }
    })
    .catch(error => console.error('Erro:', error));
}
</script>