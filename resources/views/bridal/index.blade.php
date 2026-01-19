@extends('layout.bridal')

@push('styles')
<style>
    /* Estilos específicos da Index Luminosa */
    .py-huge { padding: 140px 0; }
    .serif-italic { font-family: var(--font-serif); font-style: italic; text-transform: none; }
    
    /* HERO STYLES */
    .bridal-hero-wrap {
        height: 100vh;
        position: relative;
        display: flex;
        align-items: center;
        background: var(--bridal-cream);
        overflow: hidden;
    }

    .hero-bg-soft {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        z-index: 1;
    }

    .hero-bg-soft img {
        width: 100%; height: 100%; object-fit: cover;
        opacity: 0.6;
    }

    .hero-content-center {
        position: relative;
        z-index: 5;
        width: 100%;
    }

    .gold-subtitle {
        color: var(--bridal-gold);
        letter-spacing: 5px;
        font-size: 0.75rem;
        font-weight: 400;
    }

    .main-title-light {
        font-family: var(--font-display);
        font-size: clamp(3rem, 8vw, 6.5rem);
        color: var(--bridal-dark);
        line-height: 0.9;
        font-weight: 700;
        text-transform: uppercase;
    }

    .gold-line-center {
        width: 80px; height: 1px;
        background: var(--bridal-gold);
        margin: 30px auto;
    }

    .hero-description {
        max-width: 500px;
        color: #666;
        font-size: 1.1rem;
        font-weight: 300;
    }

    .hero-bottom-info {
        position: absolute;
        bottom: 40px; left: 50%;
        transform: translateX(-50%);
        z-index: 10;
    }

    .scroll-text {
        font-size: 0.65rem;
        letter-spacing: 3px;
        color: var(--bridal-gold);
        text-transform: uppercase;
    }

    /* BRAND TICKER */
    .brand-ticker-light {
        background: var(--bridal-white);
        padding: 40px 0;
        border-bottom: 1px solid #f4e9d5; /* soft gold */
        overflow: hidden;
    }

    .ticker-scroll {
        display: flex;
        white-space: nowrap;
        animation: tickerMove 40s linear infinite;
    }

    .brand-item-gold {
        font-size: 0.85rem;
        color: var(--bridal-gold);
        letter-spacing: 4px;
        padding: 0 40px;
        font-weight: 400;
    }

    @keyframes tickerMove {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    /* GRID & CARDS */
    .section-title-bridal {
        font-family: var(--font-display);
        font-size: 3.5rem;
        text-transform: uppercase;
        line-height: 1;
    }

    .bridal-card-main {
        position: relative;
        height: 600px;
        overflow: hidden;
        border-radius: 2px;
    }

    .bridal-card-side {
        position: relative;
        height: 288px;
        overflow: hidden;
    }

    .img-zoom {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform 2s ease;
    }

    .bridal-card-main:hover .img-zoom, .bridal-card-side:hover .img-zoom {
        transform: scale(1.08);
    }

    .card-overlay-light {
        position: absolute;
        bottom: 0; left: 0; width: 100%;
        padding: 40px;
        background: linear-gradient(to top, rgba(255,255,255,0.9), transparent);
    }

    .card-tag {
        position: absolute;
        top: 20px; right: 20px;
        background: rgba(255,255,255,0.9);
        padding: 5px 15px;
        font-size: 0.65rem;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    /* VIP SECTION */
    .vip-concierge-section {
        background: var(--bridal-cream);
        padding: 120px 0;
    }

    .image-stack {
        position: relative;
        height: 500px;
    }

    .stack-img-1 {
        width: 80%;
        height: 400px;
        object-fit: cover;
        position: absolute;
        top: 0; left: 0;
        z-index: 1;
    }

    .stack-img-2 {
        width: 50%;
        height: 350px;
        object-fit: cover;
        position: absolute;
        bottom: 0; right: 0;
        z-index: 2;
        border: 10px solid var(--bridal-white);
    }

    .btn-bridal-gold {
        background: var(--bridal-gold);
        color: white;
        padding: 18px 45px;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 3px;
        font-size: 0.75rem;
        transition: 0.4s;
        display: inline-block;
    }

    .btn-bridal-gold:hover {
        background: var(--bridal-dark);
        color: white;
        transform: translateY(-5px);
    }

    /* REVEAL ANIMATIONS */
    [data-reveal] { opacity: 0; transition: all 1.2s cubic-bezier(0.16, 1, 0.3, 1); }
    [data-reveal="scale"] { transform: scale(1.05); }
    [data-reveal="up"] { transform: translateY(40px); }
    [data-reveal="left"] { transform: translateX(-40px); }
    [data-reveal="right"] { transform: translateX(40px); }
    .revealed { opacity: 1 !important; transform: translate(0) scale(1) !important; }
</style>
@endpush

@section('content')
<div class="bridal-luminous-experience">

    <section class="bridal-hero-wrap">
        <div class="hero-bg-soft" data-reveal="scale">
            <img src="https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=2400" alt="Elegância Nupcial">
        </div>
        <div class="hero-content-center">
            <div class="container text-center">
                <span class="gold-subtitle mb-3 d-block" data-reveal="up">THE SUPREME CURATION</span>
                <h1 class="main-title-light" data-reveal="up">Pureza & <br><span class="serif-italic">Elegância</span></h1>
                <div class="gold-line-center" data-reveal="scale"></div>
                <p class="hero-description mx-auto mt-4" data-reveal="up">
                    A maior seleção de alta costura nupcial da América Latina, onde cada detalhe é um convite ao sonho.
                </p>
                <div class="mt-5" data-reveal="up">
                    <a href="#collections" class="btn-bridal-gold">Explorar Coleções</a>
                </div>
            </div>
        </div>
        <div class="hero-bottom-info">
            <span class="scroll-text">SCROLL TO DISCOVER</span>
        </div>
    </section>

    <div class="brand-ticker-light">
        <div class="ticker-scroll">
            @foreach(['ELIE SAAB', 'VERA WANG', 'PRONOVIAS', 'ROSA CLARÁ', 'GALIA LAHAV', 'MARCHESA', 'ZURHAIR MURAD'] as $brand)
                <span class="brand-item-gold">{{ $brand }}</span>
                <span class="brand-sep" style="opacity:0.3">/</span>
            @endforeach
            @foreach(['ELIE SAAB', 'VERA WANG', 'PRONOVIAS', 'ROSA CLARÁ', 'GALIA LAHAV', 'MARCHESA', 'ZURHAIR MURAD'] as $brand)
                <span class="brand-item-gold">{{ $brand }}</span>
                <span class="brand-sep" style="opacity:0.3">/</span>
            @endforeach
        </div>
    </div>

    <section id="collections" class="py-huge bg-white">
        <div class="container">
            <div class="row align-items-end mb-5">
                <div class="col-lg-6" data-reveal="left">
                    <h2 class="section-title-bridal">O Atelier <br><span class="serif-italic" style="color:var(--bridal-gold)">dos Sonhos</span></h2>
                </div>
                <div class="col-lg-5 offset-lg-1" data-reveal="right">
                    <p class="text-muted border-start ps-4">Uma imersão completa no universo Bridal. Do clássico atemporal ao vanguardismo europeu.</p>
                </div>
            </div>

            <div class="row g-4 mt-4">
                <div class="col-lg-8" data-reveal="up">
                    <div class="bridal-card-main">
                        <img src="https://images.unsplash.com/photo-1594462753934-df4119532817?q=80&w=1800" class="img-zoom" alt="Noiva SAX">
                        <div class="card-overlay-light">
                            <h3 style="font-family: var(--font-serif)">Haute Couture 2026</h3>
                            <a href="#" style="color:var(--bridal-gold); text-decoration:none; font-size:0.8rem">Ver Galeria —</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-reveal="up">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="bridal-card-side">
                                <img src="https://images.unsplash.com/photo-1594553500418-512eb930bb62?q=80&w=1000" class="img-zoom" alt="Noivo">
                                <div class="card-tag">Groom Selection</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bridal-card-side">
                                <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=1000" class="img-zoom" alt="Acessórios">
                                <div class="card-tag">Fine Jewelry</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="vip-concierge-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 py-5" data-reveal="left">
                    <div class="vip-box-info">
                        <span class="gold-subtitle">EXCLUSIVE ACCESS</span>
                        <h2 class="display-4 my-4" style="font-family: var(--font-serif);">Atendimento <br><span class="serif-italic">Private Salon</span></h2>
                        <p class="text-muted mb-5">Viva a experiência única de escolher seu traje em um ambiente privativo, com consultoria especializada.</p>
                        <a href="https://wa.me/seunumero" class="btn-bridal-gold">Agendar Cita VIP</a>
                    </div>
                </div>
                <div class="col-lg-7 ps-lg-5" data-reveal="right">
                    <div class="image-stack">
                        <img src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?q=80&w=1200" class="stack-img-1 shadow-lg" alt="Wedding">
                        <img src="https://images.unsplash.com/photo-1546193430-c2d20773d15d?q=80&w=800" class="stack-img-2 shadow-lg" alt="Salon">
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Observer para disparar animações de revelação ao scroll
        const observerOptions = { threshold: 0.15 };
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, observerOptions);

        document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el));
    });
</script>
@endpush