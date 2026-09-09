@props([
    'image',
    'link' => null,
    'alt' => 'Campanha SAX',
    'mobile' => false,
    'fallback' => null,
])

@php
    $isExternal = filled($link) && !str_starts_with($link, url('/')) && !str_starts_with($link, '/');
@endphp

<article class="catalog-editorial-banner {{ $mobile ? 'catalog-editorial-banner--mobile' : 'catalog-editorial-banner--desktop' }}">
    @if(filled($link))
        <a href="{{ $link }}" class="catalog-editorial-banner__link"
            @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
            aria-label="Abrir campanha: {{ $alt }}">
    @endif
        <span class="catalog-editorial-banner__media">
            <img src="{{ $image }}" alt="{{ $alt }}" loading="lazy" decoding="async"
                @if($fallback) onerror="this.onerror=null;this.src='{{ $fallback }}'" @endif>
        </span>
        @if(filled($link))
            <span class="catalog-editorial-banner__caption">
                <strong>Descobrir <i class="fa-solid fa-arrow-right"></i></strong>
            </span>
        @endif
    @if(filled($link))</a>@endif
</article>
