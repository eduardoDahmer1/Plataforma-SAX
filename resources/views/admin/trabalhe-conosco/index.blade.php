@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="sax-cat">

        {{-- Cabeçalho --}}
        <div class="sax-cat__top">
            <div>
                <h1 class="sax-cat__title">Trabalhe Conosco — Flyers</h1>
                <span class="sax-cat__sub">Imagens exibidas na seção pública de Trabalhe Conosco. Use ↑/↓ para definir a ordem.</span>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 rounded-0 py-2 small">{{ session('success') }}</div>
        @endif

        {{-- Upload --}}
        <form action="{{ route('admin.trabalhe_conosco.store') }}" method="POST" enctype="multipart/form-data"
              class="sax-cat__bar d-flex flex-wrap gap-2 align-items-end mb-4">
            @csrf
            <div class="flex-grow-1" style="min-width: 220px;">
                <label class="x-small fw-bold text-uppercase tracking-wider text-muted d-block mb-1">Nova imagem</label>
                <input type="file" name="image" accept="image/*" class="form-control form-control-sm rounded-0" required>
                @error('image')
                    <span class="text-danger x-small">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="sax-cat__new">
                <i class="fas fa-upload"></i> Subir flyer
            </button>
        </form>

        {{-- Lista --}}
        <div class="sax-cat__list">
            @forelse ($flyers as $flyer)
                <article class="sax-msg__item d-flex align-items-center gap-3 p-3 flex-wrap">
                    <img src="{{ asset('storage/' . $flyer->image) }}" alt="Flyer"
                         style="width: 110px; height: 70px; object-fit: cover; border: 1px solid #ece7de; border-radius: 8px;">

                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="sax-msg__label mb-0">Ordem</span>
                            <strong class="x-small">{{ $flyer->sort_order }}</strong>
                            <span class="badge rounded-pill {{ $flyer->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $flyer->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        <span class="x-small text-muted">{{ basename($flyer->image) }}</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.trabalhe_conosco.move', [$flyer, 'up']) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="sax-cat-act" title="Subir" {{ $loop->first ? 'disabled' : '' }}>
                                <i class="fa fa-arrow-up"></i> Subir
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.trabalhe_conosco.move', [$flyer, 'down']) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="sax-cat-act" title="Bajar" {{ $loop->last ? 'disabled' : '' }}>
                                <i class="fa fa-arrow-down"></i> Bajar
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.trabalhe_conosco.toggle', $flyer) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="sax-cat-act">
                                <i class="fa {{ $flyer->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                {{ $flyer->is_active ? 'Desativar' : 'Ativar' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.trabalhe_conosco.destroy', $flyer) }}"
                              data-confirm="{{ __('messages.confirmar_exclusao') }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sax-cat-act sax-cat-act--danger">
                                <i class="fa fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="sax-cat__empty">Nenhum flyer cadastrado ainda. Suba uma imagem acima.</p>
            @endforelse
        </div>
    </div>
</x-admin.card>
@endsection
