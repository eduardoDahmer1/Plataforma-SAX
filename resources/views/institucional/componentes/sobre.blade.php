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
<style>
.section-about {
    background-color: #ffffff;
    padding-top: 60px;
    margin: 0;
    border: none;
}

/* Ajustes de texto permanecem iguais */
.top-subtitle { font-family: 'Montserrat', sans-serif; color: #c5a059; text-transform: uppercase; letter-spacing: 5px; font-size: 0.8rem; font-weight: 600; display: block; margin-bottom: 1rem; }
.about-title { font-family: 'Playfair Display', serif; font-size: clamp(2.2rem, 5vw, 3.5rem); font-weight: 700; color: #1a1a1a; line-height: 1.1; }
.about-description { font-family: 'Montserrat', sans-serif; font-size: 1.05rem; line-height: 1.8; color: #555; text-align: justify; }

/* Composição de Imagem */
.image-stack { padding-right: 40px; padding-bottom: 40px; position: relative; }
.about-img-1 { width: 100%; display: block; box-shadow: 0 20px 40px rgba(0,0,0,0.05); }

/* Badge Flutuante */
.experience-card {
    position: absolute;
    bottom: 0; right: 0;
    background: #000; color: #fff;
    padding: 25px 35px;
    display: flex; align-items: center; gap: 15px;
    z-index: 2;
    border-left: 5px solid #c5a059;
}
.card-number { font-family: 'Playfair Display', serif; font-size: 3rem; font-weight: 700; color: #c5a059; line-height: 1; }
.card-text { line-height: 1.2; text-transform: uppercase; letter-spacing: 2px; font-size: 0.7rem; }

/* Ícones */
.about-features { display: flex; gap: 30px; }
.feature-item { display: flex; align-items: center; gap: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.75rem; color: #1a1a1a; }
.feature-item i { color: #c5a059; font-size: 1.3rem; }

/* --- CORREÇÃO DA BARRA (BANNER PARALLAX) --- */
.about-banner-parallax {
    height: 550px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0; /* Garante que não há margem */
    padding: 0;
    background-color: #fff; /* Se a imagem falhar, o fundo é branco como o resto */
}

.parallax-img {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%; /* Força a imagem a preencher tudo */
    width: auto;
    height: auto;
    transform: translate(-50%, -50%) !important; /* Sobrescreve cálculos de JS bugados se necessário */
    object-fit: cover;
    z-index: 1;
    filter: brightness(0.6);
}

.parallax-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.4);
    z-index: 2;
}

.parallax-title {
    font-family: 'Playfair Display', serif;
    color: #fff;
    font-size: clamp(1.8rem, 4vw, 3rem);
    position: relative;
    z-index: 3;
    max-width: 850px;
}

.parallax-line { width: 100px; height: 2px; background: #c5a059; margin: 25px auto; position: relative; z-index: 3; }

@media (max-width: 991px) {
    .section-about { padding-top: 40px; }
    .image-stack { padding: 0 0 20px 0; }
    .experience-card { padding: 20px; right: 10px; }
    .about-banner-parallax { height: 400px; }
}
</style>