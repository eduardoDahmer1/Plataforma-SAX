<section class="hero-slider">
    <div class="swiper mainSwiper">
        <div class="swiper-wrapper">
            @foreach($institucional->top_sliders as $slide)
            {{-- Tornamos o slide clicável envolvendo o conteúdo em um link --}}
            <a href="{{ route('categories.index') }}" class="swiper-slide">
                <div class="hero-overlay"></div>
                <img src="{{ asset('storage/' . $slide) }}" alt="SAX Experience">
                
                <div class="hero-content text-center">
                    <div class="container">
                        <span class="hero-subtitle" data-aos="fade-up">Exclusive Experience</span>
                        <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">
                            SAX <span class="text-gold">Department</span>
                        </h1>
                        <div class="hero-line" data-aos="zoom-in" data-aos-delay="400"></div>
                        <p class="hero-text" data-aos="fade-up" data-aos-delay="600">
                            Onde o luxo encontra a exclusividade e o design define o estilo.
                        </p>
                        <div class="hero-btn-wrapper" data-aos="fade-up" data-aos-delay="800">
                            <span class="btn-discover">Descobrir Coleção</span>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Controles elegantes --}}
        <div class="swiper-nav-wrapper d-none d-md-flex">
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>
<style>
/* Configurações da Seção */
.hero-slider {
    height: 100vh; /* Ocupa a tela cheia */
    width: 100%;
    overflow: hidden;
    background: #000;
}

.hero-slider .swiper {
    width: 100%;
    height: 100%;
}

/* Imagem e Efeito Zoom Suave */
.hero-slider .swiper-slide {
    position: relative;
    text-decoration: none;
    overflow: hidden;
}

.hero-slider .swiper-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 8s ease; /* Efeito Ken Burns lento */
}

.hero-slider .swiper-slide-active img {
    transform: scale(1.1);
}

/* Overlay Suave */
.hero-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.7) 100%);
    z-index: 1;
}

/* Conteúdo Central */
.hero-content {
    position: absolute;
    z-index: 2;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    width: 100%;
    max-width: 900px;
    padding: 0 20px;
}

/* Tipografia Elegante */
.hero-subtitle {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.85rem;
    letter-spacing: 6px;
    text-transform: uppercase;
    color: #c5a059;
    display: block;
    margin-bottom: 15px;
}

.hero-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 10vw, 5.5rem);
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 20px;
    letter-spacing: -1px;
}

.text-gold {
    color: #c5a059;
    font-style: italic;
    font-weight: 400;
}

.hero-line {
    width: 60px;
    height: 1px;
    background: #c5a059;
    margin: 0 auto 25px;
}

.hero-text {
    font-family: 'Montserrat', sans-serif;
    font-size: clamp(1rem, 2vw, 1.2rem);
    font-weight: 300;
    color: rgba(255, 255, 255, 0.8);
    max-width: 600px;
    margin: 0 auto 35px;
    line-height: 1.6;
}

/* Botão Estilizado */
.btn-discover {
    display: inline-block;
    padding: 15px 40px;
    border: 1px solid #c5a059;
    color: #fff;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 3px;
    font-weight: 600;
    transition: all 0.4s ease;
    background: transparent;
}

.hero-slider .swiper-slide:hover .btn-discover {
    background: #c5a059;
    color: #000;
    box-shadow: 0 10px 30px rgba(197, 160, 89, 0.3);
}

/* Navegação e Paginação Customizada */
.swiper-button-next, .swiper-button-prev {
    color: #fff !important;
    width: 50px;
    height: 50px;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 50%;
    transition: 0.3s;
}

.swiper-button-next:after, .swiper-button-prev:after {
    font-size: 18px !important;
}

.swiper-button-next:hover, .swiper-button-prev:hover {
    background: #c5a059;
    border-color: #c5a059;
    color: #000 !important;
}

.swiper-pagination-bullet {
    background: #fff !important;
    opacity: 0.5;
    width: 10px;
    height: 10px;
    margin: 0 6px !important;
}

.swiper-pagination-bullet-active {
    background: #c5a059 !important;
    opacity: 1;
    transform: scale(1.3);
}

/* Ajustes Responsivos Mobile */
@media (max-width: 768px) {
    .hero-slider { height: 85vh; }
    .hero-title { margin-bottom: 15px; }
    .hero-text { display: none; } /* Esconde texto longo no mobile para limpar o design */
    .btn-discover { padding: 12px 30px; font-size: 0.65rem; }
}
</style>