@extends('layout.admin')

@push('styles')
<link href="{{ asset('css/languages-admin.css') }}?v={{ filemtime(public_path('css/languages-admin.css')) }}" rel="stylesheet">
@endpush

@section('content')
@php
    $activeFilterCount = collect([
        $search !== '', filled($filtro), filled($idioma), filled($letra),
        $ordenar !== 'az', $porPagina !== 20,
    ])->filter()->count();
@endphp

<x-admin.card>
    <div class="sax-tr language-manager" id="tr-app" data-advanced="1"
         data-update-url="{{ route('admin.languages.update', ['language' => '__ID__']) }}"
         data-delete-url="{{ route('admin.languages.destroy', ['language' => '__ID__']) }}"
         data-confirm-delete="{{ __('messages.confirmar_exclusao') }}"
         data-error-message="{{ __('messages.activate_erro') }}">

        <header class="language-hero">
            <div class="language-hero__icon"><i class="fa-solid fa-language"></i></div>
            <div class="language-hero__copy">
                <span>Conteúdo multilíngue</span>
                <h1>{{ __('messages.traducoes_titulo') }}</h1>
                <p>Edite Português, Inglês e Espanhol em um só lugar.</p>
            </div>
            <div class="language-hero__actions">
                <button type="button" class="language-secondary-button" id="languageFiltersToggle"
                        aria-expanded="true" aria-controls="languageFilters">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Minimizar filtros</span>
                    @if($activeFilterCount)<b>{{ $activeFilterCount }}</b>@endif
                </button>
                <a href="{{ route('admin.languages.create') }}" class="sax-tr__new">
                    <i class="fas fa-plus"></i><span>{{ __('messages.nova_chave') }}</span>
                </a>
            </div>
        </header>

        <section class="language-stats" aria-label="Resumo das traduções">
            <a href="{{ route('admin.languages.index') }}" class="language-stat {{ !$filtro && !$idioma ? 'is-active' : '' }}">
                <span class="language-stat__icon is-blue"><i class="fa-solid fa-key"></i></span>
                <span><small>Total de chaves</small><strong>{{ number_format($total, 0, ',', '.') }}</strong></span>
            </a>
            <a href="{{ route('admin.languages.index', ['filtro' => 'completas']) }}" class="language-stat {{ $filtro === 'completas' ? 'is-active' : '' }}">
                <span class="language-stat__icon is-green"><i class="fa-solid fa-circle-check"></i></span>
                <span><small>Completas</small><strong>{{ number_format($totalCompletas, 0, ',', '.') }}</strong></span>
            </a>
            <a href="{{ route('admin.languages.index', ['filtro' => 'faltando']) }}" class="language-stat {{ $filtro === 'faltando' && !$idioma ? 'is-active' : '' }}">
                <span class="language-stat__icon is-amber"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <span><small>Incompletas</small><strong>{{ number_format($totalFaltando, 0, ',', '.') }}</strong></span>
            </a>
            <div class="language-stat language-stat--languages">
                <span class="language-stat__icon is-purple"><i class="fa-solid fa-globe"></i></span>
                <span><small>Pendências por idioma</small><strong>PT {{ $faltandoPorIdioma['pt'] }} · EN {{ $faltandoPorIdioma['en'] }} · ES {{ $faltandoPorIdioma['es'] }}</strong></span>
            </div>
        </section>

        <div class="language-filter-panel" id="languageFilters">
            <form method="GET" action="{{ route('admin.languages.index') }}" class="language-filters" id="languageFilterForm">
                <label class="language-field language-field--search">
                    <span>Buscar</span>
                    <span class="language-search-control">
                        <i class="fa fa-search"></i>
                        <input type="search" name="search" value="{{ $search }}" autocomplete="off" placeholder="Chave ou texto traduzido">
                    </span>
                </label>
                <label class="language-field">
                    <span>Situação</span>
                    <select name="filtro" data-auto-submit>
                        <option value="">Todas</option>
                        <option value="completas" @selected($filtro === 'completas')>Completas</option>
                        <option value="faltando" @selected($filtro === 'faltando')>Incompletas</option>
                    </select>
                </label>
                <label class="language-field">
                    <span>Idioma pendente</span>
                    <select name="idioma" data-auto-submit>
                        <option value="">Qualquer idioma</option>
                        <option value="pt" @selected($idioma === 'pt')>Português ({{ $faltandoPorIdioma['pt'] }})</option>
                        <option value="en" @selected($idioma === 'en')>Inglês ({{ $faltandoPorIdioma['en'] }})</option>
                        <option value="es" @selected($idioma === 'es')>Espanhol ({{ $faltandoPorIdioma['es'] }})</option>
                    </select>
                </label>
                <label class="language-field">
                    <span>Ordenar</span>
                    <select name="ordenar" data-auto-submit>
                        <option value="az" @selected($ordenar === 'az')>Chave A–Z</option>
                        <option value="za" @selected($ordenar === 'za')>Chave Z–A</option>
                        <option value="recentes" @selected($ordenar === 'recentes')>Atualizadas recentemente</option>
                    </select>
                </label>
                <label class="language-field language-field--page">
                    <span>Mostrar</span>
                    <select name="por_pagina" data-auto-submit>
                        @foreach([20, 30, 40, 50, 100] as $amount)
                            <option value="{{ $amount }}" @selected($porPagina === $amount)>{{ $amount }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="language-apply"><i class="fa-solid fa-filter"></i><span>Aplicar</span></button>
            </form>

            <nav class="language-alphabet" aria-label="Filtrar pela primeira letra">
                <a href="{{ route('admin.languages.index', request()->except('page', 'letra')) }}" class="{{ !$letra ? 'is-on' : '' }}">Todas</a>
                @foreach(array_merge(['#'], range('A', 'Z')) as $letter)
                    <a href="{{ route('admin.languages.index', array_merge(request()->except('page', 'letra'), ['letra' => $letter])) }}"
                       class="{{ $letra === $letter ? 'is-on' : '' }}">{{ $letter }}</a>
                @endforeach
            </nav>
        </div>

        <div class="language-results-bar">
            <span><i class="fa-solid fa-list"></i>
                @if($languages->total())
                    Mostrando <strong>{{ $languages->firstItem() }}–{{ $languages->lastItem() }}</strong> de <strong>{{ $languages->total() }}</strong>
                @else
                    Nenhum resultado
                @endif
            </span>
            <div class="language-results-bar__actions">
                <span class="sax-tr__hint" id="tr-feedback" role="status"></span>
                <button type="button" class="language-save-all" id="languageSaveAll" hidden>
                    <i class="fa-solid fa-check-double"></i><span>Salvar alteradas</span><b id="languageDirtyCount">0</b>
                </button>
                @if($activeFilterCount)
                    <a href="{{ route('admin.languages.index') }}"><i class="fa-solid fa-rotate-left"></i> Limpar filtros</a>
                @endif
            </div>
        </div>

        <div class="sax-tr__wrap language-table-wrap">
            <table class="sax-tr__table language-table">
                <thead><tr>
                    <th class="c-key">{{ __('messages.chave') }}</th>
                    <th><span class="language-code">PT</span> Português</th>
                    <th><span class="language-code">EN</span> Inglês</th>
                    <th><span class="language-code">ES</span> Espanhol</th>
                    <th class="c-act"><span class="visually-hidden">Ações</span></th>
                </tr></thead>
                <tbody>
                    @forelse ($languages as $l)
                        @php $incompleta = blank($l->pt) || blank($l->en) || blank($l->es); @endphp
                        <tr data-row data-id="{{ $l->id }}" data-was-missing="{{ $incompleta ? '1' : '0' }}" class="{{ $incompleta ? 'is-missing' : '' }}">
                            <td class="c-key" data-label="Chave">
                                <div class="language-key-head">
                                    <span class="language-row-status {{ $incompleta ? 'is-missing' : 'is-complete' }}">
                                        <i class="fa-solid {{ $incompleta ? 'fa-circle-exclamation' : 'fa-circle-check' }}"></i>
                                        {{ $incompleta ? 'Incompleta' : 'Completa' }}
                                    </span>
                                    <button type="button" class="language-copy" data-copy-key title="Copiar chave" aria-label="Copiar chave {{ $l->key }}"><i class="fa-regular fa-copy"></i></button>
                                </div>
                                <input type="text" name="key" value="{{ $l->key }}" spellcheck="false" aria-label="Chave">
                                <span class="c-id">#{{ $l->id }}</span>
                            </td>
                            <td data-label="Português"><input type="text" name="pt" value="{{ $l->pt }}" placeholder="Adicionar em Português" aria-label="Português"></td>
                            <td data-label="Inglês"><input type="text" name="en" value="{{ $l->en }}" placeholder="Adicionar em Inglês" aria-label="Inglês"></td>
                            <td data-label="Espanhol"><input type="text" name="es" value="{{ $l->es }}" placeholder="Adicionar em Espanhol" aria-label="Espanhol"></td>
                            <td class="c-act" data-label="Ações">
                                <button type="button" class="sax-tr__save" data-save hidden><i class="fa fa-check"></i><span>Salvar</span></button>
                                <button type="button" class="sax-tr__del" data-del title="{{ __('messages.eliminar') }}"><i class="fa fa-trash"></i><span>Excluir</span></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="sax-tr__empty">
                            <span><i class="fa-solid fa-magnifying-glass"></i></span>
                            <strong>{{ __('messages.nenhuma_traducao') }}</strong>
                            <small>Tente remover algum filtro ou buscar outro termo.</small>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($languages->hasPages())
            <div class="sax-tr__pag language-pagination">{{ $languages->onEachSide(1)->links() }}</div>
        @endif
    </div>
</x-admin.card>
@endsection
