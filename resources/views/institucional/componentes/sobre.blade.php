@php
    // Agora o componente apenas consome o que veio do Index com Fallback seguro
    $aboutTitle = $translation->section_one_title ?? $institucional->section_one_title;
    $aboutContent = $translation->section_one_content ?? $institucional->section_one_content;

    // Pool de imagens disponíveis (banners + galeria + capa), convertido em URLs para o fundo do parallax.
    // Evita repetir sempre a mesma foto de capa em todas as seções de fundo da página.
    $sceneryUrls = collect($sceneryPool ?? [])->map(fn($path) => asset('storage/' . $path))->values();
    $parallaxImage = $sceneryUrls[0] ?? ($institucional->section_one_image ? asset('storage/' . $institucional->section_one_image) : 'https://placehold.co/1920x600');
@endphp

<section id="sobre" class="section-about overflow-hidden">
    <div class="container py-lg-6 py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 position-relative" data-aos="fade-right">
                <div class="image-stack">
                    <div class="main-img-wrapper">
                        <img src="{{ $institucional->section_one_image ? asset('storage/' . $institucional->section_one_image) : 'https://placehold.co/800x600' }}" class="img-fluid about-img-1" alt="SAX Experience">
                    </div>
                    <div class="experience-card" data-aos="zoom-in" data-aos-delay="400">
                        <span class="card-number">20</span>
                        <div class="card-text">
                            <strong>{{ __('messages.anos_label') ?? 'ANOS' }}</strong>
                            <span>{{ __('messages.de_legado_label') ?? 'DE LEGADO' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 ps-lg-5 mt-4 mt-lg-0" data-aos="fade-up">
                <div class="about-text-content">
                    <span class="top-subtitle">{{ __('messages.nossa_essencia_subtitle') ?? 'Nossa Essência' }}</span>
                    <h2 class="about-title mb-4">{{ $aboutTitle }}</h2>
                    <div class="about-description">
                        {!! $aboutContent !!}
                    </div>
                    
                    <div class="about-features mt-5">
                        <div class="feature-item">
                            <i class="bi bi-gem"></i>
                            <span>{{ __('messages.curadoria_exclusiva_label') ?? 'Curadoria Exclusiva' }}</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-geo-alt"></i>
                            <span>{{ __('messages.destino_de_luxo_label') ?? 'Destino de Luxo' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="about-banner-parallax">
        <div class="parallax-overlay"></div>
        <img src="{{ $parallaxImage }}" class="parallax-img" alt="Luxury Interior"
             data-scenery-pool="{{ $sceneryUrls->toJson() }}">
        
        <div class="parallax-content text-center position-relative z-index-2">
            <h3 class="parallax-title" data-aos="zoom-out">
                {{ __('messages.banner_impacto_luxo_text') ?? 'A maior experiência de luxo da América Latina' }}
            </h3>
            <div class="parallax-line"></div>
        </div>
    </div>
</section>