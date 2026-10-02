@php
    $aboutImage = $institucional->section_one_image
        ? asset('storage/' . $institucional->section_one_image)
        : ($sceneryUrls->first() ?: asset('images/sax-og-image.jpg'));
@endphp

<section id="sobre" class="inst-about">
    <div class="container">
        <div class="inst-about__grid">
            <div class="inst-about__visual" data-aos="fade-right">
                <div class="inst-about__image"><img src="{{ $aboutImage }}" alt="Interior da SAX Department Store" loading="lazy"></div>
                <div class="inst-about__stamp">
                    <strong>{{ max(date('Y') - ($institucional->founded_year ?? 2008), 1) }}</strong>
                    <span>anos de<br>história</span>
                </div>
            </div>
            <div class="inst-about__content" data-aos="fade-up">
                <p class="inst-kicker">Nossa essência</p>
                <h2>{{ $translation->inst_section_one_title ?? $institucional->section_one_title ?? 'Muito além de uma loja' }}</h2>
                <div class="inst-prose">{!! $translation->inst_section_one_content ?? $institucional->section_one_content !!}</div>
                <div class="inst-about__signatures">
                    <span><i class="fa-solid fa-gem"></i> Curadoria internacional</span>
                    <span><i class="fa-solid fa-location-dot"></i> Paraguai</span>
                    <span><i class="fa-solid fa-star"></i> Serviço memorável</span>
                </div>
                <a href="#experiencias" class="inst-text-link">Conheça nosso universo <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>
