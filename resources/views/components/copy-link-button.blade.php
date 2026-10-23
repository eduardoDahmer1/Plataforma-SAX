@props([
    'url' => url()->current(),
])

@php
    $locale = app()->getLocale();
    $copyLabel = str_starts_with($locale, 'es')
        ? 'Copiar enlace'
        : (str_starts_with($locale, 'en') ? 'Copy link' : 'Copiar link');
    $copiedLabel = str_starts_with($locale, 'es')
        ? '¡Enlace copiado!'
        : (str_starts_with($locale, 'en') ? 'Link copied!' : 'Link copiado!');
@endphp

<button type="button"
        {{ $attributes->class(['copy-link-control']) }}
        data-copy-link
        data-copy-url="{{ $url }}"
        data-copy-label="{{ $copyLabel }}"
        data-copied-label="{{ $copiedLabel }}"
        aria-label="{{ $copyLabel }}">
    <i class="fa-solid fa-link" aria-hidden="true" data-copy-link-icon></i>
    <span class="copy-link-control__mobile-label" data-copy-link-label>{{ $copyLabel }}</span>
    <span class="copy-link-control__tooltip" role="tooltip" data-copy-link-tooltip>{{ $copyLabel }}</span>
    <span class="visually-hidden" role="status" aria-live="polite" data-copy-link-status></span>
</button>
