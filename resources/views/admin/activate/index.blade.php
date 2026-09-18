@extends('layout.admin')

@push('styles')
<link href="{{ asset('css/activate-control.css') }}?v={{ filemtime(public_path('css/activate-control.css')) }}" rel="stylesheet">
@endpush

@section('content')
@php
    $categoryActive = $categories->where('status', 1)->count();
    $brandActive = $brands->where('status', 1)->count();
@endphp
<x-admin.card>
    <div class="sax-activate activate-manager" id="activate-app"
         data-advanced="1"
         data-toggle-url="{{ route('admin.activate.toggle', ['type' => '__TYPE__', 'id' => '__ID__']) }}"
         data-error-message="{{ __('messages.activate_erro') }}">

        <header class="activate-hero">
            <div class="activate-hero__icon"><i class="fa-solid fa-sliders"></i></div>
            <div class="activate-hero__copy">
                <span>Visibilidade da loja</span>
                <h1>Controle do catálogo</h1>
                <p>Encontre e ative categorias ou marcas sem sair desta tela.</p>
            </div>
            <div class="activate-hero__stats" aria-label="Resumo do catálogo">
                <span><strong data-hero-category-active>{{ $categoryActive }}</strong> categorias ativas</span>
                <span><strong data-hero-brand-active>{{ $brandActive }}</strong> marcas ativas</span>
            </div>
            <button type="button" class="activate-collapse-all" id="activateCollapseAll">
                <i class="fa-solid fa-compress"></i><span>Minimizar tudo</span>
            </button>
        </header>

        <div class="sax-activate__bar activate-toolbar">
            <div class="sax-activate__search activate-search">
                <i class="fa fa-search"></i>
                <input type="text" id="activate-search" autocomplete="off"
                       placeholder="Buscar categoria ou marca..." aria-label="Buscar categoria ou marca">
                <button type="button" id="activateSearchClear" class="activate-search__clear" aria-label="Limpar busca" hidden>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="sax-activate__filters activate-status-filter" role="group" aria-label="Filtrar por situação">
                <button type="button" class="sax-chip activate-status-chip is-on" data-filter="all">Todas</button>
                <button type="button" class="sax-chip activate-status-chip" data-filter="1"><i class="fa-solid fa-circle"></i> Ativas</button>
                <button type="button" class="sax-chip activate-status-chip" data-filter="2"><i class="fa-regular fa-circle"></i> Inativas</button>
            </div>

            <label class="activate-select-field">
                <span>Exibir</span>
                <select id="activateScope" aria-label="Escolher tipo de conteúdo">
                    <option value="all">Categorias e marcas</option>
                    <option value="category">Somente categorias</option>
                    <option value="brand">Somente marcas</option>
                </select>
            </label>

            <label class="activate-select-field">
                <span>Ordenar</span>
                <select id="activateSort" aria-label="Ordenar resultados">
                    <option value="asc">A–Z</option>
                    <option value="desc">Z–A</option>
                </select>
            </label>

            <label class="activate-select-field activate-select-field--page">
                <span>Mostrar</span>
                <select id="activatePerPage" aria-label="Itens mostrados por seção">
                    @foreach([20, 30, 40, 50, 100] as $amount)
                        <option value="{{ $amount }}">{{ $amount }}</option>
                    @endforeach
                </select>
            </label>

            <span class="sax-activate__hint" id="activate-feedback" role="status"></span>
        </div>

        <nav class="activate-alphabet" aria-label="Filtrar pela primeira letra">
            <button type="button" class="is-on" data-letter="all">Todas</button>
            <button type="button" data-letter="#">#</button>
            @foreach(range('A', 'Z') as $letter)
                <button type="button" data-letter="{{ $letter }}">{{ $letter }}</button>
            @endforeach
        </nav>

        <div class="activate-results-summary" aria-live="polite">
            <span><i class="fa-solid fa-filter"></i> <strong id="activateResultTotal">{{ $categories->count() + $brands->count() }}</strong> resultados</span>
            <button type="button" id="activateResetFilters"><i class="fa-solid fa-rotate-left"></i> Limpar filtros</button>
        </div>

        @foreach ([
            ['tipo' => 'category', 'titulo' => __('messages.categorias'), 'icone' => 'fa-layer-group', 'itens' => $categories],
            ['tipo' => 'brand', 'titulo' => __('messages.marcas'), 'icone' => 'fa-tag', 'itens' => $brands],
        ] as $grupo)
            @php $ativos = $grupo['itens']->where('status', 1)->count(); @endphp

            <section class="sax-activate__section activate-section" data-section="{{ $grupo['tipo'] }}">
                <header class="sax-activate__head activate-section__head">
                    <button type="button" class="activate-section__toggle" data-collapse aria-expanded="true">
                        <span class="activate-section__icon"><i class="fa-solid {{ $grupo['icone'] }}"></i></span>
                        <span class="activate-section__title">
                            <strong>{{ $grupo['titulo'] }}</strong>
                            <small><span data-visible-count>{{ $grupo['itens']->count() }}</span> encontrados</small>
                        </span>
                        <span class="sax-activate__count">
                            <strong data-count-active>{{ $ativos }}</strong> / {{ $grupo['itens']->count() }} ativas
                        </span>
                        <i class="fa-solid fa-chevron-up activate-section__chevron"></i>
                    </button>
                </header>

                <div class="activate-section__body" data-section-body>
                    <div class="sax-activate__grid">
                        @foreach ($grupo['itens'] as $item)
                            @php
                                $rotulo = trim((string) $item->name) !== ''
                                    ? $item->name
                                    : ($item->slug ?: '#' . $item->id);
                            @endphp

                            <div class="ai" data-s="{{ $item->status }}">
                                <span class="ai-n" title="{{ $rotulo }}">{{ $rotulo }}</span>
                                <span class="ai-status">{{ $item->status == 1 ? 'Ativa' : 'Inativa' }}</span>
                                <button type="button" class="sax-toggle{{ $item->status == 1 ? ' is-on' : '' }}"
                                        data-id="{{ $item->id }}" aria-pressed="{{ $item->status == 1 ? 'true' : 'false' }}"
                                        aria-label="{{ $item->status == 1 ? 'Desativar' : 'Ativar' }} {{ $rotulo }}"><i></i></button>
                            </div>
                        @endforeach
                    </div>

                    <p class="sax-activate__empty" data-empty hidden>
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Nenhum resultado com estes filtros.
                    </p>

                    <nav class="activate-pagination" data-pagination aria-label="Paginação de {{ $grupo['titulo'] }}">
                        <button type="button" data-page-prev aria-label="Página anterior"><i class="fa-solid fa-chevron-left"></i><span>Anterior</span></button>
                        <strong data-page-label>Página 1</strong>
                        <button type="button" data-page-next aria-label="Próxima página"><span>Próxima</span><i class="fa-solid fa-chevron-right"></i></button>
                    </nav>
                </div>
            </section>
        @endforeach
    </div>
</x-admin.card>
@endsection
