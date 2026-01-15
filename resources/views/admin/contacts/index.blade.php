@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="h4 fw-light text-uppercase tracking-wider mb-1">Centro de Mensajes</h1>
            <p class="small text-secondary mb-0">Gestión de comunicaciones y currículos recibidos</p>
        </div>
        <a href="{{ route('admin.contacts.export', ['type' => request('type')]) }}"
           class="btn btn-outline-dark btn-sm rounded-0 px-3 text-uppercase fw-bold x-small tracking-wider">
           <i class="fas fa-download me-2"></i>Exportar CSV
        </a>
    </div>

    {{-- Filtros Estilo Tab --}}
    <div class="d-flex gap-2 mb-4 border-bottom pb-3">
        <a href="{{ route('admin.contatos.index') }}"
           class="sax-filter-link {{ request('type') === null ? 'active' : '' }}">
           TODOS
        </a>
        <a href="{{ route('admin.contatos.index', ['type' => 1]) }}"
           class="sax-filter-link {{ request('type') == 1 ? 'active' : '' }}">
           CONSULTAS
        </a>
        <a href="{{ route('admin.contatos.index', ['type' => 2]) }}"
           class="sax-filter-link {{ request('type') == 2 ? 'active' : '' }}">
           CURRÍCULOS
        </a>
    </div>

    {{-- Lista de Mensagens --}}
    <div class="row g-3">
        @forelse($contacts as $contact)
            <div class="col-12">
                <div class="card border rounded-0 shadow-none sax-message-card">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            
                            {{-- Info do Remetente --}}
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="sax-dot {{ $contact->contact_type == 2 ? 'bg-success' : 'bg-primary' }} me-2"></div>
                                    <span class="fw-bold text-dark text-uppercase small tracking-tighter">{{ $contact->name }}</span>
                                </div>
                                <div class="x-small text-muted mt-1 ps-3">
                                    {{ $contact->email }}<br>
                                    {{ $contact->phone }}
                                </div>
                            </div>

                            {{-- Conteúdo da Mensagem --}}
                            <div class="col-md-5 py-3 py-md-0 border-start-md ps-md-4">
                                <span class="sax-label mb-1">Mensaje</span>
                                <p class="small text-secondary mb-0 lh-base italic">
                                    "{{ Str::limit($contact->message, 120) }}"
                                </p>
                            </div>

                            {{-- Data e Anexo --}}
                            <div class="col-md-2 text-md-center py-2 py-md-0">
                                <span class="sax-label mb-1">Recibido</span>
                                <span class="d-block x-small fw-bold text-dark">{{ $contact->created_at->format('d/m/Y') }}</span>
                                <span class="x-small text-muted">{{ $contact->created_at->format('H:i') }}</span>
                            </div>

                            {{-- Ações --}}
                            <div class="col-md-2 text-md-end pt-3 pt-md-0">
                                <div class="d-flex justify-content-md-end gap-3 align-items-center">
                                    @if($contact->attachment)
                                        <a href="{{ asset('storage/' . $contact->attachment) }}" target="_blank" 
                                           class="text-dark text-decoration-none x-small fw-bold tracking-tighter hover-underline">
                                           VER ADJUNTO
                                        </a>
                                    @endif

                                    <form action="{{ route('admin.contatos.destroy', $contact->id) }}" method="POST" 
                                          onsubmit="return confirm('¿Eliminar mensaje?')" class="m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">
                                            ELIMINAR
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center">
                <p class="text-muted small italic">No se encontraron mensajes en esta categoría.</p>
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="mt-5 d-flex justify-content-center">
        {{ $contacts->appends(['type' => request('type')])->links() }}
    </div>
</div>

<style>
    /* UI Minimalista Inbound */
    .tracking-wider { letter-spacing: 0.12em; }
    .tracking-tighter { letter-spacing: 0.05em; }
    .x-small { font-size: 0.65rem; }
    .italic { font-style: italic; }
    
    .sax-label {
        font-size: 0.6rem;
        font-weight: 800;
        color: #bbb;
        text-transform: uppercase;
        display: block;
    }

    /* Estilo de abas de filtro */
    .sax-filter-link {
        font-size: 0.65rem;
        font-weight: 800;
        color: #999;
        text-decoration: none;
        padding: 5px 15px;
        letter-spacing: 0.1em;
        transition: 0.2s;
    }
    .sax-filter-link:hover { color: #000; }
    .sax-filter-link.active { color: #000; border-bottom: 2px solid #000; }

    /* Cards de Mensagem */
    .sax-message-card {
        transition: 0.2s;
        border-color: #eee !important;
    }
    .sax-message-card:hover {
        border-color: #000 !important;
        background-color: #fafafa;
    }

    .sax-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    /* Utilitários */
    .btn-clean { background: none; border: none; padding: 0; cursor: pointer; }
    .hover-underline:hover { text-decoration: underline !important; }
    
    @media (min-width: 768px) {
        .border-start-md { border-left: 1px solid #eee !important; }
    }
</style>
@endsection