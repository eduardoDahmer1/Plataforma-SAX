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

    {{-- Cards de mensagens --}}
    <div class="row g-3">
        @forelse($contacts as $contact)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">
                            @switch($contact->contact_type)
                                @case(1) <span class="badge bg-primary">Fale Conosco</span> @break
                                @case(2) <span class="badge bg-success">Currículo</span> @break
                                @default <span class="badge bg-secondary">Desconhecido</span>
                            @endswitch
                        </h6>

                        <h5 class="card-title mb-1">{{ $contact->name }}</h5>
                        <p class="mb-1"><i class="fas fa-envelope me-1"></i> {{ $contact->email }}</p>
                        <p class="mb-1"><i class="fas fa-phone me-1"></i> {{ $contact->phone }}</p>
                        <p class="small text-muted mb-2">{{ Str::limit($contact->message, 80) }}</p>

                        @if($contact->attachment)
                            <a href="{{ asset('storage/' . $contact->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mb-2">
                                <i class="fas fa-file me-1"></i>Ver arquivo
                            </a>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>{{ $contact->created_at->format('d/m/Y H:i') }}
                            </small>
                            <form action="{{ route('admin.contatos.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Confirma exclusão?')" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash-alt me-1"></i>Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-4">
                Nenhuma mensagem encontrada.
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="mt-3">
        {{ $contacts->appends(['type' => request('type')])->links() }}
    </div>
</div>
@endsection
