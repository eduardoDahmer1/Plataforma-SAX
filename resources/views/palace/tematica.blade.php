<section class="arabe-premium-section" id="noite-arabe">
    <div class="container">
        <div class="row align-items-center g-5">
            {{-- Coluna de Texto --}}
            <div class="col-lg-5 order-2 order-lg-1" data-aos="fade-up">
                <div class="content-wrapper">
                    <div class="premium-badge mb-3">
                        <span class="line"></span>
                        <span class="text">{{ $palace->tematica_tag ?? 'Experiência Temática' }}</span>
                    </div>
                    
                    <h2 class="arabe-title mb-4">
                        {{ $palace->tematica_titulo }}
                    </h2>
                    
                    <div class="arabe-description mb-5">
                        {!! nl2br(e($palace->tematica_descricao)) !!}
                    </div>

                    <div class="d-flex align-items-center gap-4 flex-wrap">
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" 
                           target="_blank" 
                           class="btn-arabe-gold">
                           <span>Reservar Agora</span>
                           <i class="fab fa-whatsapp ms-2"></i>
                        </a>
                        
                        {{-- Preço visível no Mobile de forma integrada --}}
                        <div class="mobile-price-tag d-md-none">
                            <span class="price">{{ $palace->tematica_preco }}</span>
                            <span class="unit">/ pessoa</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna da Imagem com Efeito --}}
            <div class="col-lg-7 order-1 order-lg-2" data-aos="fade-left">
                <div class="image-reveal-container">
                    <div class="image-border-decoration"></div>
                    <div class="image-wrapper">
                        <img src="{{ asset('storage/' . $palace->tematica_imagem) }}"
                            class="img-main" 
                            alt="{{ $palace->tematica_titulo }}">
                        
                        {{-- Badge de Preço Desktop Flutuante --}}
                        <div class="floating-price-card d-none d-md-block">
                            <div class="card-inner">
                                <span class="label">Inversión</span>
                                <span class="amount">{{ $palace->tematica_preco }}</span>
                                <span class="sub">Por Persona</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
    /* Container Principal */
.arabe-premium-section {
    padding: 100px 0;
    background-color: #171717; /* Tom de papel creme luxo */
    overflow: hidden;
}

/* Tipografia e Títulos */
.arabe-title {
    font-family: 'Playfair Display', serif; /* Certifique-se de ter essa fonte ou use uma serifada */
    font-size: clamp(2.5rem, 5vw, 4rem);
    color: #ffffff;
    line-height: 1.1;
    font-weight: 700;
}

.premium-badge {
    display: flex;
    align-items: center;
    gap: 15px;
}

.premium-badge .line {
    width: 40px;
    height: 2px;
    background: #D4AF37;
}

.premium-badge .text {
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 3px;
    color: #D4AF37;
    text-transform: uppercase;
}

.arabe-description {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #555;
    max-width: 90%;
}

/* Imagem e Efeitos de Reveal */
.image-reveal-container {
    position: relative;
    padding: 20px;
}

.image-wrapper {
    position: relative;
    border-radius: 5px;
    overflow: visible; /* Para o badge flutuar fora */
    z-index: 2;
}

.img-main {
    width: 100%;
    height: 550px;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 20px 20px 60px rgba(0,0,0,0.1);
    transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
}

/* Efeito de Hover na Imagem */
.image-reveal-container:hover .img-main {
    transform: scale(1.03);
}

.image-border-decoration {
    position: absolute;
    top: 0;
    right: 0;
    width: 80%;
    height: 90%;
    border: 2px solid #D4AF37;
    z-index: 1;
    transform: translate(30px, -30px);
    border-radius: 10px;
}

/* Badge de Preço Flutuante (Desktop) */
.floating-price-card {
    position: absolute;
    bottom: 40px;
    right: -20px;
    background: #1a1a1a;
    color: white;
    padding: 30px;
    border-radius: 0;
    box-shadow: 15px 15px 40px rgba(0,0,0,0.2);
    z-index: 3;
    animation: float 4s ease-in-out infinite;
}

.floating-price-card .card-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.floating-price-card .amount {
    font-size: 2.5rem;
    font-weight: 700;
    color: #D4AF37;
    line-height: 1;
}

.floating-price-card .label, .floating-price-card .sub {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    opacity: 0.8;
}

/* Botão Luxo */
.btn-arabe-gold {
    display: inline-flex;
    align-items: center;
    padding: 18px 40px;
    background: #1a1a1a;
    color: #D4AF37;
    text-decoration: none;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: 1px solid #1a1a1a;
}

.btn-arabe-gold:hover {
    background: #D4AF37;
    color: #1a1a1a;
    transform: translateY(-5px);
}

/* Tag de Preço Mobile */
.mobile-price-tag {
    background: #D4AF37;
    padding: 10px 20px;
    color: #1a1a1a;
}

.mobile-price-tag .price {
    font-weight: 800;
    font-size: 1.2rem;
}

.mobile-price-tag .unit {
    font-size: 0.7rem;
    text-transform: uppercase;
    font-weight: 700;
}

/* Animação Flutuante */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
}

/* Responsividade */
@media (max-width: 991px) {
    .arabe-premium-section { padding: 60px 0; }
    .img-main { height: 400px; }
    .image-border-decoration { display: none; }
    .arabe-description { max-width: 100%; }
    .arabe-title { text-align: center; }
    .content-wrapper { text-align: center; display: flex; flex-direction: column; align-items: center; }
}
</style>