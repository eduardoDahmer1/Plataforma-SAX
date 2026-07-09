@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.conteudo_editorial_titulo') }}"
        description="Exibindo <span class='text-dark fw-bold'>{{ $blogs->count() }}</span> de {{ $blogs->total() }} artigos registrados">
        <x-slot:actions>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-white border rounded-0 btn-sm fw-bold x-small tracking-wider px-3">
                    {{ __('messages.categorias_btn') }}
                </a>
                <a href="{{ route('admin.blogs.create') }}" class="btn btn-dark rounded-0 btn-sm fw-bold x-small tracking-wider px-3">
                    <i class="fa fa-plus me-1"></i> {{ __('messages.novo_artigo_btn') }}
                </a>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <form action="{{ route('admin.blogs.index') }}" method="GET" id="filterForm">
        <div class="sax-search-wrapper mb-3">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 px-3">
                            <i class="fa fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0 sax-search-input"
                            placeholder="Buscar por título, subtítulo ou slug..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-dark rounded-3 px-3" type="submit">
                        <i class="fa fa-sliders-h me-2"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>

        <div class="sax-filter-bar">
            <div class="sax-filter-item">
                <label class="sax-filter-label">{{ __('messages.categorias') }}</label>
                <select name="category_id" class="form-select sax-filter-select" onchange="this.form.submit()">
                    <option value="">Todas as Categorias</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sax-filter-item">
                <label class="sax-filter-label">Status</label>
                <select name="status_filter" class="form-select sax-filter-select" onchange="this.form.submit()">
                    <option value="">Todos os status</option>
                    <option value="active" @selected(request('status_filter') == 'active')>{{ __('messages.status_publicado') }}</option>
                    <option value="draft" @selected(request('status_filter') == 'draft')>{{ __('messages.status_rascunho') }}</option>
                </select>
            </div>

            <div class="sax-filter-item">
                <label class="sax-filter-label">{{ __('messages.ordenar_por') }}</label>
                <select name="sort_by" class="form-select sax-filter-select" onchange="this.form.submit()">
                    <option value="">Mais recentes</option>
                    <option value="oldest" @selected(request('sort_by') == 'oldest')>Mais antigos</option>
                    <option value="title_az" @selected(request('sort_by') == 'title_az')>Título (A–Z)</option>
                    <option value="title_za" @selected(request('sort_by') == 'title_za')>Título (Z–A)</option>
                </select>
            </div>

            <div class="sax-filter-item">
                <label class="sax-filter-label">Exibir</label>
                <select name="per_page" class="form-select sax-filter-select" onchange="this.form.submit()">
                    @foreach([30, 40, 50] as $opt)
                        <option value="{{ $opt }}" @selected(request('per_page', 30) == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if(request()->hasAny(['search', 'category_id', 'status_filter', 'sort_by']))
            <div class="mt-2 text-end">
                <a href="{{ route('admin.blogs.index') }}" class="sax-clear-filters">
                    <i class="fa fa-times me-1"></i> Limpar filtros
                </a>
            </div>
        @endif
    </form>

    <div class="sax-admin-list">
        @forelse($blogs as $blog)
        <div class="sax-admin-card mb-2">
            <div class="row align-items-center g-0">
                <div class="col-auto">
                    <div class="sax-card-img-box">
                        <img src="{{ $blog->image ? Storage::url($blog->image) : asset('storage/uploads/noimage.webp') }}" alt="">
                    </div>
                </div>

                <div class="col ps-3 min-w-0">
                    <div class="mb-1 d-flex align-items-center gap-2">
                        <span class="badge bg-light text-muted border rounded-0 x-small-7 fw-bold">
                            {{ $blog->category->name ?? 'STYLE' }}
                        </span>
                        <span class="status-pill {{ $blog->is_active ? 'active' : 'draft' }}">
                            {{ $blog->is_active ? __('messages.status_publicado') : __('messages.status_rascunho') }}
                        </span>
                    </div>
                    <h6 class="fw-bold mb-1 text-uppercase small tracking-tighter text-truncate">{{ $blog->title }}</h6>
                    @if($blog->subtitle)
                        <p class="x-small text-muted mb-0 text-truncate">{{ $blog->subtitle }}</p>
                    @endif
                </div>

                <div class="col-auto px-4 d-none d-lg-block border-start h-100">
                    <div class="text-center">
                        <span class="d-block x-small-7 text-muted fw-bold tracking-wider">{{ __('messages.publicacao_label') }}</span>
                        <span class="d-block small fw-bold">{{ $blog->published_at ? $blog->published_at->format('d M, Y') : '—' }}</span>
                    </div>
                </div>

                <div class="col-auto px-4 border-start">
                    <div class="d-flex gap-3">
                        <a href="{{ route('blogs.show', $blog->slug) }}" target="_blank" class="action-icon" title="{{ __('messages.vista_previa') }}">
                            <i class="far fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.blogs.edit', $blog) }}" class="action-icon" title="{{ __('messages.editar') }}">
                            <i class="far fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirmar_eliminar_geral') }}')" class="m-0">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-icon text-danger">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white border p-5 text-center">
            <p class="text-muted mb-0">{{ __('messages.nenhum_artigo') }}</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $blogs->links('pagination::bootstrap-4') }}
    </div>
</x-admin.card>
@endsection
