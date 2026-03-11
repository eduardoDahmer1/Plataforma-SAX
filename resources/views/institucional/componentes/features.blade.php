<section class="features-elegant py-5">
    <div class="container py-5">
        <div class="row g-4 justify-content-center">
            {{-- Feature 01 --}}
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-feature">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-gem"></i>
                    </div>
                    <div class="feature-divider"></div>
                    <h3 class="feature-title">{{ $institucional->text_section_one_title }}</h3>
                    <p class="feature-body">{{ $institucional->text_section_one_body }}</p>
                </div>
            </div>

            {{-- Feature 02 (Destaque Central) --}}
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-feature active">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-star"></i>
                    </div>
                    <div class="feature-divider"></div>
                    <h3 class="feature-title">{{ $institucional->text_section_two_title }}</h3>
                    <p class="feature-body">{{ $institucional->text_section_two_body }}</p>
                </div>
            </div>

            {{-- Feature 03 --}}
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card-feature">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="feature-divider"></div>
                    <h3 class="feature-title">{{ $institucional->text_section_three_title }}</h3>
                    <p class="feature-body">{{ $institucional->text_section_three_body }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
/* Fundo Off-White Suave */
.features-elegant {
    background-color: #fcfcfc;
    position: relative;
}

/* Card Principal */
.card-feature {
    background: #ffffff;
    padding: 3rem 2rem;
    text-align: center;
    border: 1px solid rgba(197, 160, 89, 0.1); /* Borda dourada quase invisível */
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03); /* Sombra muito suave */
}

/* Efeito de Destaque no Hover e Active */
.card-feature.active, 
.card-feature:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
    border-color: rgba(197, 160, 89, 0.4);
}

/* Traço dourado superior fino */
.card-feature::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background: #c5a059;
    transition: width 0.5s ease;
}

.card-feature:hover::before,
.card-feature.active::before {
    width: 100%;
}

/* Ícones */
.feature-icon-wrapper i {
    font-size: 2.5rem;
    color: #c5a059;
    margin-bottom: 1.5rem;
    display: inline-block;
    transition: transform 0.4s ease;
}

.card-feature:hover .feature-icon-wrapper i {
    transform: scale(1.1);
}

/* Divisória fina dourada entre título e ícone */
.feature-divider {
    width: 40px;
    height: 1px;
    background: #c5a059;
    margin: 1rem auto 1.5rem;
    opacity: 0.6;
}

/* Tipografia Elegante */
.feature-title {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 1.4rem;
    color: #2c2c2c; /* Preto suave (antracite) */
    margin-bottom: 1rem;
    letter-spacing: 0.5px;
}

.feature-body {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.95rem;
    line-height: 1.7;
    color: #666666; /* Cinza médio elegante */
    font-weight: 400;
}

/* Ajustes Mobile */
@media (max-width: 768px) {
    .card-feature {
        padding: 2rem 1.5rem;
    }
}
</style>