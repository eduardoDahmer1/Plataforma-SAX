@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="sax-tr" id="tr-app"
         data-update-url="{{ route('admin.languages.update', ['language' => '__ID__']) }}"
         data-delete-url="{{ route('admin.languages.destroy', ['language' => '__ID__']) }}" data-confirm-delete="{{ __('messages.confirmar_exclusao') }}" data-error-message="{{ __('messages.activate_erro') }}">

        {{-- Cabeçalho --}}
        <div class="sax-tr__top">
            <div>
                <h1 class="sax-tr__title">{{ __('messages.traducoes_titulo') }}</h1>
                <span class="sax-tr__sub">
                    {{ __('messages.mostrando') }} {{ $languages->total() }} {{ __('messages.chaves_de_traducao') }}
                    @if ($totalFaltando > 0)
                        · <a href="{{ route('admin.languages.index', ['filtro' => 'faltando']) }}" class="sax-tr__warn">
                            {{ __('messages.traducao_faltando', ['n' => $totalFaltando]) }}
                        </a>
                    @endif
                </span>
            </div>

            <a href="{{ route('admin.languages.create') }}" class="sax-tr__new">
                <i class="fas fa-plus"></i> {{ __('messages.nova_chave') }}
            </a>
        </div>

        {{-- Busca e filtro (server-side: a lista é paginada) --}}
        <div class="sax-tr__bar">
            <form method="GET" action="{{ route('admin.languages.index') }}" class="sax-tr__search">
                <i class="fa fa-search"></i>
                <input type="text" name="search" value="{{ $search }}" autocomplete="off"
                       placeholder="{{ __('messages.buscar_placeholder') }}">
                @if ($filtro)
                    <input type="hidden" name="filtro" value="{{ $filtro }}">
                @endif
            </form>

            <div class="sax-tr__chips">
                <a href="{{ route('admin.languages.index', ['search' => $search]) }}"
                   class="sax-chip {{ !$filtro ? 'is-on' : '' }}">{{ __('messages.cupon_situacao_todas') }}</a>
                <a href="{{ route('admin.languages.index', ['search' => $search, 'filtro' => 'faltando']) }}"
                   class="sax-chip {{ $filtro === 'faltando' ? 'is-on' : '' }}">{{ __('messages.traducao_incompletas') }}</a>
                @if ($search || $filtro)
                    <a href="{{ route('admin.languages.index') }}" class="sax-chip"><i class="fa fa-times"></i></a>
                @endif
            </div>

            <span class="sax-tr__hint" id="tr-feedback" role="status"></span>
        </div>

        {{-- Tabela --}}
        <div class="sax-tr__wrap">
            <table class="sax-tr__table">
                <thead>
                    <tr>
                        <th class="c-key">{{ __('messages.chave') }}</th>
                        <th>PT</th>
                        <th>EN</th>
                        <th>ES</th>
                        <th class="c-act"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($languages as $l)
                        @php $incompleta = blank($l->en) || blank($l->es); @endphp

                        <tr data-row data-id="{{ $l->id }}" class="{{ $incompleta ? 'is-missing' : '' }}">
                            <td class="c-key">
                                <input type="text" name="key" value="{{ $l->key }}" spellcheck="false">
                                <span class="c-id">#{{ $l->id }}</span>
                            </td>
                            <td><input type="text" name="pt" value="{{ $l->pt }}"></td>
                            <td><input type="text" name="en" value="{{ $l->en }}" placeholder="—"></td>
                            <td><input type="text" name="es" value="{{ $l->es }}" placeholder="—"></td>
                            <td class="c-act">
                                {{-- O salvar só acende quando algo muda na linha --}}
                                <button type="button" class="sax-tr__save" data-save hidden>
                                    <i class="fa fa-check"></i>
                                </button>
                                <button type="button" class="sax-tr__del" data-del title="{{ __('messages.eliminar') }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="sax-tr__empty">{{ __('messages.nenhuma_traducao') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="sax-tr__pag">{{ $languages->links() }}</div>
    </div>
</x-admin.card>
@endsection
