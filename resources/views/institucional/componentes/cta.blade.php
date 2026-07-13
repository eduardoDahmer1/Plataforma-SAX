@php
    $ctaDesc = $translation->section_one_content ?? $institucional->section_one_content;

    // Pool de imagens disponíveis (banners + galeria + capa); pega uma diferente das usadas no parallax e nos stats
    $sceneryUrls = collect($sceneryPool ?? [])->map(fn($path) => asset('storage/' . $path))->values();
    $ctaImage = $sceneryUrls[2 % max($sceneryUrls->count(), 1)] ?? ($institucional->section_one_image ? asset('storage/' . $institucional->section_one_image) : 'https://placehold.co/1920x600');
@endphp

{{-- SECTION: CTA FINAL --}}
<section class="cta-luxury">
    <div class="cta-bg-image" style="background-image: url('{{ $ctaImage }}')" data-scenery-pool="{{ $sceneryUrls->toJson() }}"></div>
    <div class="cta-overlay"></div>
    <div class="container position-relative z-index-2">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8" data-aos="fade-up">
                <span class="cta-pre-title">{{ __('messages.cta_pre_title') ?? 'O Destino do Luxo' }}</span>
                <h2 class="cta-main-title">{{ __('messages.cta_main_title') ?? 'Pronto para uma experiência inesquecível?' }}</h2>
                <p class="cta-desc">
                    {{ __('messages.cta_desc_override') ?? 'Visite a maior loja de departamentos de luxo da América Latina e descubra um mundo de sofisticação em Ciudad del Este e Assunção.' }}
                </p>
                <a href="{{ route('contact.form') }}" class="btn-luxury-gold">
                    {{ __('messages.entrar_em_contato_btn') ?? 'ENTRAR EM CONTATO' }} <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- SECTION: TIMELINE HISTÓRICA --}}
<section class="timeline-section py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="top-subtitle">{{ __('messages.timeline_subtitle') ?? 'Nossa Jornada' }}</span>
            <h2 class="section-title-elegant">{{ __('messages.timeline_title') ?? 'Timeline SAX' }}</h2>
            <div class="title-divider mx-auto"></div>
        </div>

        <div class="main-timeline">
            {{-- 2008 - Origem --}}
            <div class="timeline-container left" data-aos="fade-right">
                <div class="timeline-date">2008</div>
                <div class="timeline-box">
                    <span class="timeline-tag">{{ __('messages.timeline_tag_sobre') ?? 'Sobre Nosotros' }}</span>
                    <h4>{{ __('messages.timeline_2008_title') ?? 'O Nascimento de um Sonho Único' }}</h4>
                    <p>{{ __('messages.timeline_2008_text') ?? 'Armando Nasser dá vida a um sonho empresarial único: S.A.X. A visão de fundir o que há de melhor em decoração, moda, gastronomia e mercado de luxo se materializa em Ciudad del Este e Assunção, no Paraguai. Ao longo dos anos, tornou-se uma joia rara entre as boutiques, abrigando prestigiadas marcas internacionais com uma arquitetura autêntica e serviço excepcional que definem um alto padrão de prestígio e distinção no cenário latino-americano.' }}</p>
                </div>
            </div>

            {{-- 2023 - Expansão --}}
            <div class="timeline-container right" data-aos="fade-left">
                <div class="timeline-date">2023</div>
                <div class="timeline-box">
                    <span class="timeline-tag">{{ __('messages.timeline_tag_asuncion') ?? 'Sax Asunción' }}</span>
                    <h4>{{ __('messages.timeline_2023_expansion_title') ?? 'Liderança e Expansão Latina' }}</h4>
                    <p>{{ __('messages.timeline_2023_expansion_text') ?? 'O SAX consolida-se como líder na América Latina, reunindo mais de 200 marcas internacionais. Em Ciudad del Este, sua presença imponente estende-se por 17.000 m² em 11 pisos, enquanto em Assunção ocupa 3.600 m² no prestigiado Paseo la Galería. Um oásis de elegância projetado para proporcionar uma experiência de compra autêntica, combinando style distinto e arquitetura moderna como o epicentro da sofisticação.' }}</p>
                </div>
            </div>

            {{-- 2023 - Noivas --}}
            <div class="timeline-container left" data-aos="fade-right">
                <div class="timeline-date">2023</div>
                <div class="timeline-box">
                    <span class="timeline-tag">{{ __('messages.timeline_tag_bridal') ?? 'Sax Bridal' }}</span>
                    <h4>{{ __('messages.timeline_2023_bridal_title') ?? 'A Excelência do Grande Dia' }}</h4>
                    <p>{{ __('messages.timeline_2023_bridal_text') ?? 'A Sax Bridal oferece uma experiência personalizada para as noivas em cada etapa do caminho até o altar. Com oficina especializada para ajustes exclusivos e consultoria de imagem dedicada, a missão é realçar a beleza interior e exterior. O atendimento reflete o entendimento de que cada noiva é única, cuidando com delicadeza de cada detalhe do vestido para garantir tranquilidade e uma experiência de compra inesquecível.' }}</p>
                </div>
            </div>

            {{-- 2023 - Gastronomia --}}
            <div class="timeline-container right" data-aos="fade-left">
                <div class="timeline-date">2023</div>
                <div class="timeline-box">
                    <span class="timeline-tag">{{ __('messages.timeline_tag_palace') ?? 'Sax Palace' }}</span>
                    <h4>{{ __('messages.timeline_2023_palace_title') ?? 'Gastronomia e Ambiente Excepcional' }}</h4>
                    <p>{{ __('messages.timeline_2023_palace_text') ?? 'No Sax Palace, a experiência gastronômica é elevada por um ambiente meticulosamente concebido, acolhedor e elegante. Com iluminação suave e uma curadoria musical relaxante, o espaço abriga um bar e adega com extensa seleção de vinhos internacionais conduzida por sommeliers experientes. Além de coquetéis artesanais criativos, oferecemos a cortesia de não aplicar taxa de rolha para bebidas adquiridas em nossa garrafeira.' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>