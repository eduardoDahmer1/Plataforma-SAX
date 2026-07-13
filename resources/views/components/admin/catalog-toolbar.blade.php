@props([
    'title',
    'subtitle' => null,
    'createUrl' => null,
    'createLabel' => null,
    'searchAction' => null,
    'searchPlaceholder' => null,
])

{{-- Cabeçalho + busca comuns às listagens do catálogo (marcas, categorias, subcategorias, filhas) --}}
<div class="sax-cat__top">
    <div>
        <h1 class="sax-cat__title">{{ $title }}</h1>
        @if ($subtitle)
            <span class="sax-cat__sub">{!! $subtitle !!}</span>
        @endif
    </div>

    @if ($createUrl)
        <a href="{{ $createUrl }}" class="sax-cat__new">
            <i class="fas fa-plus"></i> {{ $createLabel ?? __('messages.novo') }}
        </a>
    @endif
</div>

@if ($searchAction)
    <div class="sax-cat__bar">
        <form method="GET" action="{{ $searchAction }}" class="sax-cat__search">
            <i class="fa fa-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                   placeholder="{{ $searchPlaceholder ?? __('messages.buscar_placeholder') }}">
        </form>

        @if (request('search'))
            <a href="{{ $searchAction }}" class="sax-cat__clear">
                <i class="fa fa-times"></i>
            </a>
        @endif
    </div>
@endif
