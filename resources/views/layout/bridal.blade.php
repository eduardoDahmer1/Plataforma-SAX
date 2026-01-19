<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SAX BRIDAL | The Haute Couture Experience</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@200;300;400;500&family=Cinzel:wght@400;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* --- DESIGN SYSTEM --- */
        :root {
            --bridal-white: #ffffff;
            --bridal-cream: #fdfbf8;
            --bridal-gold: #c5a059;
            --bridal-gold-light: #e2d1b0;
            --bridal-dark: #121212;
            --font-serif: 'Playfair Display', serif;
            --font-sans: 'Montserrat', sans-serif;
            --font-accent: 'Cinzel', serif;
            --transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        body {
            background-color: var(--bridal-white);
            color: var(--bridal-dark);
            font-family: var(--font-sans);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* --- NAVIGATION LUX --- */
        .navbar-sax {
            position: fixed;
            top: 0; width: 100%; height: 100px;
            z-index: 2000;
            transition: var(--transition);
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(0,0,0,0);
        }

        .navbar-sax.scrolled {
            height: 80px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(197, 160, 89, 0.2);
        }

        .nav-brand-sax {
            font-family: var(--font-accent);
            font-size: 1.8rem;
            letter-spacing: 8px;
            color: var(--bridal-dark);
            text-decoration: none;
            transition: var(--transition);
        }

        .nav-link-sax {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--bridal-dark);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            padding: 5px 0;
        }

        .nav-link-sax::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; width: 0; height: 1px;
            background: var(--bridal-gold);
            transition: var(--transition);
        }

        .nav-link-sax:hover::after { width: 100%; }

        /* --- HERO SECTION --- */
        .hero-bridal {
            height: 100vh;
            position: relative;
            background: var(--bridal-cream);
            overflow: hidden;
            display: flex;
            align-items: center;
        }

        .hero-video-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
            opacity: 0.7;
            filter: sepia(20%) brightness(90%);
        }

        .hero-content {
            position: relative;
            z-index: 10;
            width: 100%;
        }

        .hero-title {
            font-family: var(--font-accent);
            font-size: clamp(3rem, 10vw, 7rem);
            line-height: 0.9;
            margin-bottom: 20px;
            color: var(--bridal-dark);
        }

        .hero-subtitle {
            font-family: var(--font-serif);
            font-style: italic;
            font-size: 1.5rem;
            color: var(--bridal-gold);
            margin-bottom: 40px;
        }

        /* --- TICKER MARCAS --- */
        .brand-ticker {
            background: var(--bridal-white);
            padding: 30px 0;
            border-bottom: 1px solid #eee;
            overflow: hidden;
            display: flex;
        }

        .ticker-track {
            display: flex;
            white-space: nowrap;
            animation: tickerScroll 40s linear infinite;
        }

        .ticker-item {
            font-family: var(--font-accent);
            font-size: 0.8rem;
            letter-spacing: 4px;
            padding: 0 50px;
            color: #999;
        }

        @keyframes tickerScroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* --- SECTION STYLES --- */
        .section-padding { padding: 120px 0; }

        .title-gold {
            color: var(--bridal-gold);
            font-family: var(--font-accent);
            font-size: 0.8rem;
            letter-spacing: 5px;
            display: block;
            margin-bottom: 15px;
        }

        .h2-editorial {
            font-family: var(--font-serif);
            font-size: 3.5rem;
            line-height: 1.1;
        }

        /* --- BRIDAL CARDS --- */
        .card-bridal {
            position: relative;
            overflow: hidden;
            aspect-ratio: 4/5;
            background: #eee;
        }

        .card-bridal img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 1.5s ease;
        }

        .card-bridal:hover img { transform: scale(1.08); }

        .card-bridal-info {
            position: absolute;
            bottom: 0; left: 0; width: 100%;
            padding: 30px;
            background: linear-gradient(to top, rgba(255,255,255,0.95), transparent);
            transform: translateY(20px);
            opacity: 0;
            transition: var(--transition);
        }

        .card-bridal:hover .card-bridal-info {
            transform: translateY(0);
            opacity: 1;
        }

        /* --- BUTTONS --- */
        .btn-sax {
            padding: 18px 45px;
            background: var(--bridal-dark);
            color: white;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 0.7rem;
            border: none;
            transition: var(--transition);
            display: inline-block;
            text-decoration: none;
        }

        .btn-sax:hover {
            background: var(--bridal-gold);
            color: white;
            transform: translateY(-5px);
        }

        .btn-sax-outline {
            padding: 15px 40px;
            border: 1px solid var(--bridal-gold);
            color: var(--bridal-gold);
            text-transform: uppercase;
            letter-spacing: 3px;
            font-size: 0.7rem;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-sax-outline:hover {
            background: var(--bridal-gold);
            color: white;
        }

        /* --- VIP SECTION --- */
        .vip-section {
            background: var(--bridal-cream);
            position: relative;
        }

        .image-collage {
            position: relative;
            height: 600px;
        }

        .img-stack-main {
            width: 80%; height: 500px;
            object-fit: cover;
            position: absolute; top: 0; left: 0;
            box-shadow: 20px 20px 60px rgba(0,0,0,0.05);
        }

        .img-stack-sub {
            width: 50%; height: 300px;
            object-fit: cover;
            position: absolute; bottom: 0; right: 0;
            border: 15px solid var(--bridal-white);
            box-shadow: 10px 10px 40px rgba(0,0,0,0.1);
        }

        /* --- REVEAL ANIMATIONS --- */
        [data-reveal] { opacity: 0; transition: all 1.2s ease; }
        [data-reveal="up"] { transform: translateY(50px); }
        [data-reveal="left"] { transform: translateX(-50px); }
        [data-reveal="right"] { transform: translateX(50px); }
        .revealed { opacity: 1 !important; transform: translate(0) !important; }

        /* --- FOOTER --- */
        .footer-sax {
            background: var(--bridal-white);
            padding: 100px 0 50px;
            border-top: 1px solid #f0f0f0;
        }

        @media (max-width: 991px) {
            .navbar-sax { height: 70px; }
            .h2-editorial { font-size: 2.5rem; }
            .image-collage { height: 450px; margin-top: 50px; }
        }
    </style>
</head>
<body>

    <nav class="navbar-sax">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-none d-lg-flex gap-4">
                <a href="#collections" class="nav-link-sax">Coleções</a>
                <a href="#experience" class="nav-link-sax">Experiência</a>
            </div>

            <a href="/" class="nav-brand-sax">SAX <span style="font-weight:200">BRIDAL</span></a>

            <div class="d-flex align-items-center gap-4">
                <a href="https://wa.me/seunumerosax" class="nav-link-sax d-none d-lg-block">Agendamento</a>
                <a href="/" class="nav-link-sax d-none d-lg-block">loja</a>
                <button class="btn border-0 p-0" id="menuToggle">
                    <div style="width: 25px; height: 1px; background: var(--bridal-dark); margin-bottom: 6px;"></div>
                    <div style="width: 15px; height: 1px; background: var(--bridal-dark); margin-left: auto;"></div>
                </button>
            </div>
        </div>
    </nav>

    <section class="hero-bridal">
        <img src="https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=2400" class="hero-video-bg" alt="Bridal Experience">
        
        <div class="hero-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8" data-reveal="up">
                        <span class="title-gold">HAUTE COUTURE EXPERIENCE</span>
                        <h1 class="hero-title">Onde o <br>Sonho Começa.</h1>
                        <p class="hero-subtitle">Curadoria internacional exclusiva no coração da SAX.</p>
                        <div class="d-flex gap-3">
                            <a href="#collections" class="btn-sax">Ver Coleções</a>
                            <a href="#experience" class="btn-sax-outline">Vip Lounge</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="brand-ticker">
        <div class="ticker-track">
            @foreach(['ELIE SAAB', 'VERA WANG', 'PRONOVIAS', 'ROSA CLARÁ', 'GALIA LAHAV', 'MARCHESA', 'ZURHAIR MURAD', 'OSCAR DE LA RENTA'] as $brand)
                <span class="ticker-item">{{ $brand }}</span>
            @endforeach
            @foreach(['ELIE SAAB', 'VERA WANG', 'PRONOVIAS', 'ROSA CLARÁ', 'GALIA LAHAV', 'MARCHESA', 'ZURHAIR MURAD', 'OSCAR DE LA RENTA'] as $brand)
                <span class="ticker-item">{{ $brand }}</span>
            @endforeach
        </div>
    </div>

    <section id="collections" class="section-padding">
        <div class="container">
            <div class="row align-items-end mb-5">
                <div class="col-lg-6" data-reveal="left">
                    <span class="title-gold">01 — CURADORIA</span>
                    <h2 class="h2-editorial">A Arte da <br><span style="font-style: italic">Silhueta Perfeita</span></h2>
                </div>
                <div class="col-lg-5 offset-lg-1" data-reveal="right">
                    <p class="text-muted mb-4">Selecionamos as grifes mais prestigiadas do mundo para garantir que cada noiva e noivo encontre a expressão máxima de sua personalidade.</p>
                </div>
            </div>

            <div class="row g-4 mt-5">
                <div class="col-lg-8" data-reveal="up">
                    <div class="card-bridal">
                        <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?q=80&w=1200&auto=format&fit=crop" alt="Bridal">
                        <div class="card-bridal-info">
                            <span class="title-gold">COLEÇÃO 2026</span>
                            <h4 class="h3 fw-light">Wedding Gowns</h4>
                            <a href="#" class="link-dark text-decoration-none small letter-spacing-2 mt-3 d-block">EXPLORAR <i class="fa-solid fa-arrow-right-long ms-2"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="row g-4">
                        <div class="col-12" data-reveal="right">
                            <div class="card-bridal" style="aspect-ratio: 1/1;">
                                <img src="https://images.unsplash.com/photo-1583939003579-730e3918a45a?q=80&w=1200&auto=format&fit=crop" alt="Groom">
                                <div class="card-bridal-info">
                                    <h4 class="h5 fw-light">Groom Sartoria</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-12" data-reveal="right">
                            <div class="card-bridal" style="aspect-ratio: 1/1;">
                                <img src="https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?q=80&w=1200&auto=format&fit=crop" alt="Accessories">
                                <div class="card-bridal-info">
                                    <h4 class="h5 fw-light">Joalheria & Acessórios</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="experience" class="section-padding vip-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-reveal="left">
                    <div class="image-collage">
                        <img src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?q=80&w=1500" class="img-stack-main" alt="Vip Salon">
                        <img src="https://images.unsplash.com/photo-1516997121675-4c2d1684aa3e?q=80&w=1000&auto=format&fit=crop" class="img-stack-sub" alt="Champagne">
                    </div>
                </div>
                <div class="col-lg-5 offset-lg-1" data-reveal="right">
                    <span class="title-gold">02 — PRIVATE LOUNGE</span>
                    <h2 class="h2-editorial mb-4">Um Atendimento <br>À Sua Altura.</h2>
                    <p class="text-muted mb-5">Na SAX Bridal, sua escolha é celebrada. Oferecemos salas privativas, consultoria de imagem e um serviço de concierge exclusivo para tornar este dia inesquecível antes mesmo de ele começar.</p>
                    <a href="https://wa.me/seunumerosax" class="btn-sax">Reservar Horário VIP</a>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer-sax text-center">
        <div class="container">
            <h2 class="nav-brand-sax mb-5">SAX BRIDAL</h2>
            <div class="d-flex justify-content-center gap-5 mb-5">
                <a href="#" class="nav-link-sax">Coleções</a>
                <a href="#" class="nav-link-sax">Atendimento</a>
                <a href="#" class="nav-link-sax">Privacidade</a>
            </div>
            <div class="social-links mb-5">
                <a href="#" class="mx-3 text-dark fs-5"><i class="fab fa-instagram"></i></a>
                <a href="#" class="mx-3 text-dark fs-5"><i class="fab fa-pinterest"></i></a>
                <a href="#" class="mx-3 text-dark fs-5"><i class="fab fa-whatsapp"></i></a>
            </div>
            <p class="small text-muted letter-spacing-2">© 2026 SAX PALACE GROUP. A DEFINIÇÃO DO LUXO.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // NAVBAR SCROLL EFFECT
            const navbar = document.querySelector('.navbar-sax');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // INTERSECTION OBSERVER PARA REVELAÇÕES
            const observerOptions = {
                threshold: 0.15
            };

            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el));
            
            // SMOOTH SCROLL PARA ÂNCORAS
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>