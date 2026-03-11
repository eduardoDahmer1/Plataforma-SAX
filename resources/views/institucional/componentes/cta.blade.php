{{-- SECTION: CTA FINAL --}}
<section class="cta-luxury">
    <div class="cta-bg-image" style="background-image: url('{{ asset('storage/' . $institucional->section_one_image) }}')"></div>
    <div class="cta-overlay"></div>
    <div class="container position-relative z-index-2">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8" data-aos="fade-up">
                <span class="cta-pre-title">O Destino do Luxo</span>
                <h2 class="cta-main-title">Pronto para uma experiência inesquecível?</h2>
                <p class="cta-desc">Visite a maior loja de departamentos de luxo da América Latina e descubra um mundo de sofisticação em Ciudad del Este e Assunção.</p>
                <a href="{{ route('contact.form') }}" class="btn-luxury-gold">
                    ENTRAR EM CONTATO <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- SECTION: TIMELINE HISTÓRICA --}}
<section class="timeline-section py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="top-subtitle">Nossa Jornada</span>
            <h2 class="section-title-elegant">Timeline SAX</h2>
            <div class="title-divider mx-auto"></div>
        </div>

        <div class="main-timeline">
            <div class="timeline-container left" data-aos="fade-right">
                <div class="timeline-date">2008</div>
                <div class="timeline-box">
                    <span class="timeline-tag">Sobre Nosotros</span>
                    <h4>O Nascimento de um Sonho Único</h4>
                    <p>Armando Nasser dá vida a um sonho empresarial único: S.A.X. A visão de fundir o que há de melhor em decoração, moda, gastronomia e mercado de luxo se materializa em Ciudad del Este e Assunção, no Paraguai. Ao longo dos anos, tornou-se uma joia rara entre as boutiques, abrigando prestigiadas marcas internacionais com uma arquitetura autêntica e serviço excepcional que definem um alto padrão de prestígio e distinção no cenário latino-americano.</p>
                </div>
            </div>

            <div class="timeline-container right" data-aos="fade-left">
                <div class="timeline-date">2023</div>
                <div class="timeline-box">
                    <span class="timeline-tag">Sax Asunción</span>
                    <h4>Liderança e Expansão Latina</h4>
                    <p>O SAX consolida-se como líder na América Latina, reunindo mais de 200 marcas internacionais. Em Ciudad del Este, sua presença imponente estende-se por 17.000 m² em 11 pisos, enquanto em Assunção ocupa 3.600 m² no prestigiado Paseo la Galería. Um oásis de elegância projetado para proporcionar uma experiência de compra autêntica, combinando estilo distinto e arquitetura moderna como o epicentro da sofisticação.</p>
                </div>
            </div>

            <div class="timeline-container left" data-aos="fade-right">
                <div class="timeline-date">2023</div>
                <div class="timeline-box">
                    <span class="timeline-tag">Sax Bridal</span>
                    <h4>A Excelência do Grande Dia</h4>
                    <p>A Sax Bridal oferece uma experiência personalizada para as noivas em cada etapa do caminho até o altar. Com oficina especializada para ajustes exclusivos e consultoria de imagem dedicada, a missão é realçar a beleza interior e exterior. O atendimento reflete o entendimento de que cada noiva é única, cuidando com delicadeza de cada detalhe do vestido para garantir tranquilidade e uma experiência de compra inesquecível.</p>
                </div>
            </div>

            <div class="timeline-container right" data-aos="fade-left">
                <div class="timeline-date">2023</div>
                <div class="timeline-box">
                    <span class="timeline-tag">Sax Palace</span>
                    <h4>Gastronomia e Ambiente Excepcional</h4>
                    <p>No Sax Palace, a experiência gastronômica é elevada por um ambiente meticulosamente concebido, acolhedor e elegante. Com iluminação suave e uma curadoria musical relaxante, o espaço abriga um bar e adega com extensa seleção de vinhos internacionais conduzida por sommeliers experientes. Além de coquetéis artesanais criativos, oferecemos a cortesia de não aplicar taxa de rolha para bebidas adquiridas em nossa garrafeira.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
/* --- CTA LUXURY STYLE --- */
.cta-luxury {
    position: relative;
    padding: 140px 0;
    background: #000;
    overflow: hidden;
}
.cta-bg-image {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    z-index: 1;
}
.cta-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: linear-gradient(180deg, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.9) 100%);
    z-index: 2;
}
.cta-pre-title { color: #c5a059; text-transform: uppercase; letter-spacing: 7px; font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 1.5rem; }
.cta-main-title { font-family: 'Playfair Display', serif; font-size: clamp(2rem, 5vw, 3.8rem); color: #fff; font-weight: 700; margin-bottom: 1.5rem; line-height: 1.1; }
.cta-desc { color: rgba(255,255,255,0.8); font-size: 1.15rem; max-width: 800px; margin: 0 auto 3rem; font-family: 'Montserrat', sans-serif; font-weight: 300; line-height: 1.6; }
.btn-luxury-gold {
    display: inline-block;
    padding: 20px 55px;
    background: #c5a059;
    color: #000;
    text-decoration: none;
    font-weight: 700;
    letter-spacing: 3px;
    font-size: 0.75rem;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}
.btn-luxury-gold:hover { background: #fff; transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.4); }

/* --- TIMELINE REFINADA --- */
.main-timeline {
    position: relative;
    max-width: 1100px;
    margin: 50px auto;
}
.main-timeline::after {
    content: '';
    position: absolute;
    width: 1px;
    background: rgba(197, 160, 89, 0.2);
    top: 0; bottom: 0;
    left: 50%;
    margin-left: -0.5px;
}
.timeline-container {
    padding: 20px 60px;
    position: relative;
    width: 50%;
}
.timeline-container::after {
    content: '';
    position: absolute;
    width: 14px; height: 14px;
    right: -7px; top: 40px;
    background: #c5a059;
    border: 3px solid #fff;
    border-radius: 50%;
    z-index: 1;
    box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.1);
}
.right::after { left: -7px; }

.left { left: 0; text-align: right; }
.right { left: 50%; text-align: left; }

.timeline-date {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 700;
    color: #c5a059;
    line-height: 1;
    margin-bottom: 10px;
}
.timeline-tag {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #c5a059;
    font-weight: 700;
    display: block;
    margin-bottom: 10px;
}
.timeline-box {
    background: #ffffff;
    padding: 40px;
    border: 1px solid #f2f2f2;
    box-shadow: 0 15px 45px rgba(0,0,0,0.03);
    transition: 0.4s ease;
}
.timeline-box:hover {
    border-color: rgba(197, 160, 89, 0.3);
    box-shadow: 0 20px 50px rgba(0,0,0,0.06);
    transform: translateY(-5px);
}
.timeline-box h4 { 
    font-family: 'Playfair Display', serif;
    font-weight: 700; 
    font-size: 1.25rem; 
    margin-bottom: 15px; 
    color: #1a1a1a;
}
.timeline-box p { 
    color: #666; 
    font-size: 0.95rem; 
    line-height: 1.8; 
    margin: 0;
    text-align: justify;
}

/* RESPONSIVIDADE MOBILE */
@media (max-width: 991px) {
    .main-timeline::after { left: 31px; }
    .timeline-container { 
        width: 100%; 
        padding-left: 70px; 
        padding-right: 20px; 
        text-align: left !important;
        margin-bottom: 40px;
    }
    .timeline-container::after { left: 24px; top: 45px; }
    .right { left: 0; }
    .timeline-date { font-size: 2rem; }
    .timeline-box { padding: 30px; }
    .timeline-box p { text-align: left; }
}
</style>