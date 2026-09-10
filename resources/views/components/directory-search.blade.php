@props([
    'action',
    'placeholder',
    'value' => '',
    'clearUrl' => null,
])

<form method="GET" action="{{ $action }}" class="sax-directory-search" role="search">
    <i class="fas fa-search sax-directory-search__icon" aria-hidden="true"></i>
    <input type="search" name="search" placeholder="{{ $placeholder }}" value="{{ $value }}"
        aria-label="{{ $placeholder }}">
    @if (filled($value) && filled($clearUrl))
        <a href="{{ $clearUrl }}" class="sax-directory-search__clear" aria-label="{{ __('messages.limpar_busca') }}">
            <i class="fas fa-times" aria-hidden="true"></i>
        </a>
    @endif
    <button type="submit">
        <span>{{ __('messages.buscar') }}</span>
        <i class="fas fa-arrow-right" aria-hidden="true"></i>
    </button>
</form>
