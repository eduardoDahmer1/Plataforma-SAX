@php
    $sliders = sax_rotate_images($institucional->top_sliders ?: [], 1);
    $heroTitle = $translation->inst_section_one_title ?? $institucional->section_one_title ?? 'SAX Department Store';
@endphp

<section class="inst-hero" aria-label="SAX Department Store">
    <div class="swiper mainSwiper" data-autoplay="{{ ($institucional->hero_autoplay_seconds ?? 6) * 1000 }}">
        <div class="swiper-wrapper">
            @forelse($sliders as $slide)
                <article class="swiper-slide inst-hero__slide">
                    <img src="{{ asset('storage/' . $slide) }}" alt="Experiência SAX Department Store" fetchpriority="{{ $loop->first ? 'high' : 'auto' }}">
                    <div class="inst-hero__veil"></div>
                    <div class="container inst-hero__content">
                        <p class="inst-kicker" data-aos="fade-up">{{ $copy('inst_hero_eyebrow', 'Experiência exclusiva') }}</p>
                        <h1 data-aos="fade-up" data-aos-delay="120">{{ $heroTitle }}</h1>
                        <p class="inst-hero__lead" data-aos="fade-up" data-aos-delay="220">{{ $copy('inst_hero_description', 'Moda, gastronomia, celebrações e hospitalidade reunidas em um destino singular no Paraguai.') }}</p>
                        <a href="#sobre" class="inst-button inst-button--outline" data-aos="fade-up" data-aos-delay="320">
                            {{ $copy('inst_hero_cta', 'Descobrir a SAX') }} <i class="fa-solid fa-arrow-down"></i>
                        </a>
                    </div>
                </article>
            @empty
                <article class="swiper-slide inst-hero__slide inst-hero__slide--fallback">
                    <div class="inst-hero__veil"></div>
                    <div class="container inst-hero__content">
                        <p class="inst-kicker">{{ $copy('inst_hero_eyebrow', 'Experiência exclusiva') }}</p>
                        <h1>{{ $heroTitle }}</h1>
                        <p class="inst-hero__lead">{{ $copy('inst_hero_description', 'O destino onde a curadoria encontra a experiência.') }}</p>
                    </div>
                </article>
            @endforelse
        </div>
        <div class="inst-hero__nav">
            <button class="inst-hero__arrow swiper-button-prev" type="button" aria-label="Slide anterior"></button>
            <div class="swiper-pagination"></div>
            <button class="inst-hero__arrow swiper-button-next" type="button" aria-label="Próximo slide"></button>
        </div>
    </div>
    <a class="inst-hero__scroll" href="#sobre"><span>Explore</span><i class="fa-solid fa-arrow-down"></i></a>
</section>
