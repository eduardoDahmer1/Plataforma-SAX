{{-- SAX Café & Bistrô — Hero --}}
<section class="hero-cafe" id="hero">

    {{-- Imagem de fundo dinâmica --}}
    @if($cafeBistro->hero_imagen)
        <img src="{{ asset('storage/'.$cafeBistro->hero_imagen) }}"
             class="hero-cafe-bg" alt="" fetchpriority="high" decoding="async">
    @endif

    {{-- Overlay sobre a imagem de fundo --}}
    <div class="hero-overlay"></div>

    <div class="container hero-content">
        @if(($cafeBistro->slug ?? null) === 'asuncion')
            <div class="asu-location-badge" data-reveal="up">
                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                <span>{{ $cafeBistro->name }}</span>
            </div>
        @endif
        <span class="eyebrow">{{ __('messages.cafe_hero_eyebrow') }}</span>

        <h1 class="hero-title" data-reveal="up">
            {{ $t?->cafe_hero_titulo ?? $cafeBistro->hero_titulo ?? __('messages.cafe_hero_title_fallback') }}
        </h1>

        <p class="hero-subtitle" data-reveal="up">
            {{ $t?->cafe_hero_subtitulo ?? $cafeBistro->hero_subtitulo ?? __('messages.cafe_hero_subtitle_fallback') }}
        </p>

        <div class="hero-actions" data-reveal="up">
            <a href="{{ $cafeBistro->whatsapp_link }}" target="_blank" class="btn-cafe-primary">
                @if(($cafeBistro->slug ?? null) === 'asuncion')<i class="bi bi-calendar2-heart" aria-hidden="true"></i>@endif
                {{ __('messages.cafe_reserve_table') }}
            </a>
            <a href="#cardapio" class="btn-cafe-outline">
                @if(($cafeBistro->slug ?? null) === 'asuncion')<i class="bi bi-journal-richtext" aria-hidden="true"></i>@endif
                {{ __('messages.cafe_view_menu') }}
            </a>
        </div>
    </div>

    @if(($cafeBistro->slug ?? null) === 'asuncion')
        <a class="asu-scroll-cue" href="#sobre" aria-label="{{ __('messages.sobre_nos') }}">
            <span></span><i class="bi bi-arrow-down" aria-hidden="true"></i>
        </a>
    @endif

</section>
