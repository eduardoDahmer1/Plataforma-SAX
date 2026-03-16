<section id="sobre" class="section-about overflow-hidden">
    <div class="container py-lg-6 py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 position-relative" data-aos="fade-right">
                <div class="image-stack">
                    <div class="main-img-wrapper">
                        <img src="{{ asset('storage/' . $institucional->section_one_image) }}" class="img-fluid about-img-1" alt="SAX Experience">
                    </div>
                    <div class="experience-card" data-aos="zoom-in" data-aos-delay="400">
                        <span class="card-number">20</span>
                        <div class="card-text">
                            <strong>ANOS</strong>
                            <span>DE LEGADO</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 ps-lg-5 mt-4 mt-lg-0" data-aos="fade-up">
                <div class="about-text-content">
                    <span class="top-subtitle">Nossa Essência</span>
                    <h2 class="about-title mb-4">{{ $institucional->section_one_title }}</h2>
                    <div class="about-description">
                        {!! nl2br(e($institucional->section_one_content)) !!}
                    </div>
                    
                    <div class="about-features mt-5">
                        <div class="feature-item">
                            <i class="bi bi-gem"></i>
                            <span>Curadoria Exclusiva</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-geo-alt"></i>
                            <span>Destino de Luxo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Banner de Impacto - Ajustado para evitar vazamento de fundo --}}
    <div class="about-banner-parallax">
        <div class="parallax-overlay"></div>
        <img src="{{ asset('storage/' . ($institucional->section_two_image ?? $institucional->section_one_image)) }}" class="parallax-img" alt="Luxury Interior">
        
        <div class="parallax-content text-center position-relative z-index-2">
            <h3 class="parallax-title" data-aos="zoom-out">A maior experiência de luxo da América Latina</h3>
            <div class="parallax-line"></div>
        </div>
    </div>
</section>
