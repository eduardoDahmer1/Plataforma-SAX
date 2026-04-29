@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Traduções"
        description="Mostrando {{ $languages->count() }} chaves de tradução"
        actionUrl="{{ route('admin.languages.create') }}"
        actionLabel="Nova Chave" />

    <div class="card border-0 shadow-sm p-3 mb-4" style="border-radius: 15px;">
        <form action="{{ route('admin.languages.index') }}" method="GET">
            <div class="input-group">
                <input type="text" 
                       name="search" 
                       id="searchLanguage" 
                       class="form-control border-0 bg-light" 
                       placeholder="Buscar por chave ou termo..." 
                       value="{{ $search ?? '' }}"
                       style="border-radius: 8px 0 0 8px;">
                <button type="submit" class="btn btn-dark px-4" style="border-radius: 0 8px 8px 0; background: #000;">
                    <i class="fas fa-search mr-1"></i> BUSCAR
                </button>
                @if($search)
                    <a href="{{ route('admin.languages.index') }}" class="btn btn-outline-secondary ml-2" style="border-radius: 8px;">Limpar</a>
                @endif
            </div>
        </form>
    </div>

    <div class="row">
        @forelse($languages as $l)
        <div class="col-12 col-md-6 col-lg-4 mb-4 language-item">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; transition: transform 0.2s;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-light text-dark p-2" style="font-family: monospace; font-size: 0.85rem; border: 1px solid #eee;">
                            {{ $l->key }}
                        </span>
                        <span class="text-muted small">ID: {{ $l->id }}</span>
                    </div>

                    <div class="mb-2">
                        <label class="small font-weight-bold text-uppercase mb-0" style="font-size: 10px; color: #999;">Português</label>
                        <p class="mb-2 text-truncate" style="font-weight: 600; color: #333;">{{ $l->pt }}</p>
                    </div>

                    <div class="mb-2">
                        <label class="small font-weight-bold text-uppercase mb-0" style="font-size: 10px; color: #999;">Inglês / Espanhol</label>
                        <p class="mb-0 small text-muted">
                            <span class="badge bg-white border mr-1">EN</span> {{ Str::limit($l->en, 30) }}
                        </p>
                        <p class="mb-0 small text-muted">
                            <span class="badge bg-white border mr-1">ES</span> {{ Str::limit($l->es, 30) }}
                        </p>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 p-4 pt-0">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('admin.languages.edit', $l->id) }}" class="btn btn-outline-dark btn-sm w-100 font-weight-bold" style="border-radius: 8px; border: 1px solid #e0e0e0;">
                                <i class="fas fa-edit mr-1"></i> EDITAR
                            </a>
                        </div>
                        <div class="col-6">
                            <form action="{{ route('admin.languages.destroy', $l->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100 font-weight-bold" style="border-radius: 8px; border: 1px solid #ffebeb;" onclick="return confirm('Deseja excluir esta tradução?')">
                                    <i class="fas fa-trash mr-1"></i> ELIMINAR
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p class="text-muted">Nenhuma tradução encontrada para "{{ $search }}".</p>
        </div>
        @endforelse
    </div>
</x-admin.card>

<style>
    .language-item:hover {
        transform: translateY(-5px);
    }
    .badge {
        letter-spacing: 0.5px;
    }
    .btn-outline-dark:hover {
        background-color: #000;
        color: #fff;
    }
    .text-truncate {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endsection