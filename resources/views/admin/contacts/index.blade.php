@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.centro_mensagens_titulo') }}"
        description="{{ __('messages.gestao_comunicacoes_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.contacts.export', ['type' => request('type')]) }}"
               class="btn btn-outline-dark btn-sm rounded-0 px-3 text-uppercase fw-bold x-small tracking-wider">
               <i class="fas fa-download me-2"></i> {{ __('messages.exportar_csv_btn') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filtros Estilo Tab --}}
    <div class="d-flex gap-2 mb-4 border-bottom pb-3">
        <a href="{{ route('admin.contatos.index') }}"
           class="sax-filter-link {{ request('type') === null ? 'active' : '' }}">
           {{ __('messages.filtro_todos') }}
        </a>
        <a href="{{ route('admin.contatos.index', ['type' => 1]) }}"
           class="sax-filter-link {{ request('type') == 1 ? 'active' : '' }}">
           {{ __('messages.filtro_consultas') }}
        </a>
        <a href="{{ route('admin.contatos.index', ['type' => 2]) }}"
           class="sax-filter-link {{ request('type') == 2 ? 'active' : '' }}">
           {{ __('messages.filtro_curriculos') }}
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
                                <span class="sax-label mb-1">{{ __('messages.label_mensagem') }}</span>
                                <p class="small text-secondary mb-0 lh-base italic">
                                    "{{ Str::limit($contact->message, 120) }}"
                                </p>
                            </div>

                            {{-- Data e Anexo --}}
                            <div class="col-md-2 text-md-center py-2 py-md-0">
                                <span class="sax-label mb-1">{{ __('messages.label_recebido') }}</span>
                                <span class="d-block x-small fw-bold text-dark">{{ $contact->created_at->format('d/m/Y') }}</span>
                                <span class="x-small text-muted">{{ $contact->created_at->format('H:i') }}</span>
                            </div>

                            {{-- Ações --}}
                            <div class="col-md-2 text-md-end pt-3 pt-md-0">
                                <div class="d-flex justify-content-md-end gap-3 align-items-center">
                                    @if($contact->attachment)
                                        <a href="{{ asset('storage/' . $contact->attachment) }}" target="_blank"
                                           class="text-dark text-decoration-none x-small fw-bold tracking-tighter hover-underline">
                                           {{ __('messages.ver_anexo_link') }}
                                        </a>
                                    @endif

                                    <form action="{{ route('admin.contatos.destroy', $contact->id) }}" method="POST"
                                          onsubmit="return confirm('{{ __('messages.confirmar_eliminar_msg') }}')" class="m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">
                                            {{ __('messages.eliminar_btn') }}
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
                <p class="text-muted small italic">{{ __('messages.nenhuma_mensagem_aviso') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="mt-5 d-flex justify-content-center">
        {{ $contacts->appends(['type' => request('type')])->links() }}
    </div>
</x-admin.card>
@endsection