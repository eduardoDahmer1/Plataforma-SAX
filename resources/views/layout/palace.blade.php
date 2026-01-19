<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description" content="SAX Palace - Experiência gastronômica e eventos de luxo no coração de Ciudad del Este.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#000000">

    <title>SAX Palace - Gastronomia & Eventos de Luxo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Montserrat:wght@200;300;400;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />

    <style>
        :root {
            --palace-dark: #0a0a0a;
            --palace-dark-soft: #141414;
            --palace-gold: #c5a059;
            --palace-gold-hover: #e2b86d;
            --palace-text: #ffffff;
            --palace-muted: #a0a0a0;
            --transition-smooth: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Reset & Base */
        body {
            background-color: var(--palace-dark);
            color: var(--palace-text);
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1, h2, h3, h4, .font-serif {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        .gold-text { color: var(--palace-gold); }
        .letter-spacing-2 { letter-spacing: 2px; }
        .letter-spacing-5 { letter-spacing: 5px; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--palace-dark); }
        ::-webkit-scrollbar-thumb { background: var(--palace-gold); border-radius: 10px; }

        /* Header e Navegação */
        .palace-nav {
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(197, 160, 89, 0.15);
            padding: 1.2rem 0;
            z-index: 1050;
            transition: var(--transition-smooth);
        }

        .palace-nav.scrolled { padding: 0.8rem 0; background: #000; }

        .palace-nav .nav-link {
            color: var(--palace-text) !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            margin: 0 15px;
            position: relative;
            transition: var(--transition-smooth);
        }

        .palace-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 1px;
            background: var(--palace-gold);
            transition: var(--transition-smooth);
        }

        .palace-nav .nav-link:hover::after { width: 100%; }
        .palace-nav .nav-link:hover { color: var(--palace-gold) !important; }

        /* Botões */
        .btn-palace {
            border: 1px solid var(--palace-gold);
            color: var(--palace-gold);
            background: transparent;
            padding: 12px 30px;
            border-radius: 0; /* Design Luxury costuma ser quadrado ou totalmente redondo */
            text-transform: uppercase;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 2px;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
            display: inline-block;
            text-decoration: none;
        }

        .btn-palace:hover {
            background: var(--palace-gold);
            color: #000;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(197, 160, 89, 0.2);
        }

        /* Seções Gerais */
        .section-padding { padding: 100px 0; }
        .bg-palace-soft { background-color: var(--palace-dark-soft); }
        
        .gold-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--palace-gold), transparent);
            width: 150px;
            margin: 30px auto;
        }

        .section-title {
            margin-bottom: 60px;
        }

        .section-title span {
            display: block;
            color: var(--palace-gold);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 4px;
            margin-bottom: 10px;
        }

        /* Cards Gastronomia */
        .food-card {
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: var(--transition-smooth);
        }

        .food-card img {
            transition: transform 1.5s ease;
            filter: grayscale(30%);
        }

        .food-card:hover img {
            transform: scale(1.1);
            filter: grayscale(0%);
        }

        .food-card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 40px;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
        }

        /* Bodega & Vinhos */
        .bodega-item {
            border-left: 1px solid rgba(197, 160, 89, 0.3);
            padding-left: 30px;
            margin-bottom: 40px;
            transition: var(--transition-smooth);
        }

        .bodega-item:hover {
            border-left-color: var(--palace-gold);
            transform: translateX(10px);
        }

        .bodega-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: rgba(197, 160, 89, 0.2);
            line-height: 1;
        }

        /* Footer */
        .palace-footer {
            background: #000;
            padding-top: 80px;
            border-top: 1px solid rgba(197, 160, 89, 0.1);
        }

        .footer-logo img { height: 60px; margin-bottom: 30px; }
        
        .footer-links ul { list-style: none; padding: 0; }
        .footer-links li { margin-bottom: 15px; }
        .footer-links a {
            color: var(--palace-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: var(--transition-smooth);
        }
        .footer-links a:hover { color: var(--palace-gold); padding-left: 5px; }

        .contact-info i {
            width: 30px;
            color: var(--palace-gold);
        }

        .social-circle {
            width: 45px;
            height: 45px;
            border: 1px solid rgba(197, 160, 89, 0.3);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin-right: 10px;
            transition: var(--transition-smooth);
        }

        .social-circle:hover {
            background: var(--palace-gold);
            color: #000;
            border-color: var(--palace-gold);
        }

        .copyright {
            background: #050505;
            padding: 25px 0;
            margin-top: 80px;
            font-size: 0.75rem;
            color: #555;
            letter-spacing: 1px;
        }

        /* Floating Menu Button */
        .menu-float {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 999;
            background: rgba(197, 160, 89, 0.9);
            color: #000;
            padding: 15px 25px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            cursor: pointer;
            transition: var(--transition-smooth);
        }

        .menu-float:hover { background: #fff; transform: scale(1.05); }

        @media (max-width: 991px) {
            .section-padding { padding: 60px 0; }
            .display-4 { font-size: 2.5rem; }
        }
    </style>
</head>

<body>

    <div class="menu-float">
        <i class="bi bi-journal-text me-2"></i> Nosso Menu
    </div>

    <header class="palace-nav sticky-top" id="mainHeader">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="logo">
                <a href="/">
                    <img src="{{ asset('images/logo-sax-white.png') }}" alt="SAX Palace" height="45">
                </a>
            </div>
            
            <nav class="d-none d-lg-block">
                <ul class="nav">
                    <li class="nav-item"><a href="#" class="nav-link">Institucional</a></li>
                    <li class="nav-item"><a href="#" class="nav-link">SAX Bridal</a></li>
                    <li class="nav-item"><a href="#" class="nav-link">Bodega</a></li>
                    <li class="nav-item"><a href="#" class="nav-link">Eventos</a></li>
                    <li class="nav-item"><a href="#" class="nav-link">Contato</a></li>
                </ul>
            </nav>

            <div class="header-actions d-flex align-items-center">
                <a href="#reservas" class="btn-palace d-none d-sm-block">
                    Reservar <i class="fa fa-calendar-check ms-2"></i>
                </a>
                <button class="btn text-white d-lg-none ms-3" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
                    <i class="bi bi-list fs-1"></i>
                </button>
            </div>
        </div>
        
        <div class="collapse d-lg-none bg-black" id="mobileNav">
            <ul class="nav flex-column p-4 text-center">
                <li class="nav-item"><a href="#" class="nav-link py-3">Institucional</a></li>
                <li class="nav-item"><a href="#" class="nav-link py-3">Bodega</a></li>
                <li class="nav-item"><a href="#" class="nav-link py-3">Eventos</a></li>
                <li class="nav-item"><a href="#reservas" class="nav-link py-3 gold-text">Reservas</a></li>
            </ul>
        </div>
    </header>

    <main>
        @yield('content')
        
        <section class="section-padding bg-palace-soft" id="noite-arabe">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5" data-aos="fade-right">
                        <div class="section-title text-start mb-4">
                            <span>Experiência Temática</span>
                            <h2 class="display-4">Noite Árabe</h2>
                        </div>
                        <p class="lead text-secondary mb-4">
                            A culinária árabe é um conjunto de diversas culturas e é semelhante à Cozinha Mediterrânica, 
                            mas com toques do Médio Oriente.
                        </p>
                        <p class="mb-5">
                            Toda Sexta-feira você pode desfrutar de uma experiência única e maravilhosa no SAX Palace. 
                            A noite árabe com show de dança do ventre! Nosso buffet variado custa 24 U$ por pessoa, bebidas não incluídas.
                        </p>
                        <a href="#" class="btn-palace">Mais Informações</a>
                    </div>
                    <div class="col-lg-7 mt-5 mt-lg-0" data-aos="zoom-in">
                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1533619239233-62815d2214b5" class="img-fluid rounded shadow-lg" alt="Noite Árabe SAX">
                            <div class="position-absolute bottom-0 end-0 bg-gold p-4 d-none d-md-block" style="background: var(--palace-gold); transform: translate(20px, 20px);">
                                <h4 class="text-black mb-0">24 U$</h4>
                                <small class="text-black text-uppercase fw-bold">Por Pessoa</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-padding container" id="contato">
            <div class="row g-5">
                <div class="col-md-6" data-aos="fade-up">
                    <h2 class="display-5 mb-4 font-serif">Onde Estamos</h2>
                    <div class="gold-divider ms-0 mb-4"></div>
                    <p class="mb-4">Avda. San Blas Esq. Regimiento Sauce Piso 11 Ciudad del Este, 7000, Paraguai</p>
                    
                    <div class="contact-box p-4 border border-secondary mb-4">
                        <h5 class="gold-text mb-3">Horário de Atendimento</h5>
                        <p class="small mb-2">Segunda-feira: 08:30 às 17:00</p>
                        <p class="small mb-2">Terça a Sábado: 08:20 às 23:45</p>
                        <p class="small">Domingo: 09:00 às 16:00</p>
                    </div>

                    <div class="d-flex align-items-center">
                        <i class="fab fa-whatsapp fs-3 gold-text me-3"></i>
                        <div>
                            <span class="d-block small text-uppercase">Reservas WhatsApp</span>
                            <strong>+595 981 528186</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m12!1m3!1d3600.6322!2d-54.6105!3d-25.5126!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94f6908a7985834b%3A0x6e266185854743c!2sSAX%20Department%20Store!5e0!3m2!1spt-BR!2spy!4v1700000000000" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="palace-footer">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-logo">
                        <img src="{{ asset('images/logo-sax-white.png') }}" alt="SAX Logo">
                    </div>
                    <p class="text-secondary pe-lg-5">
                        O SAX Palace redefine o conceito de luxo e sofisticação em Ciudad del Este, 
                        proporcionando momentos inesquecíveis em um ambiente exclusivo no 11º andar.
                    </p>
                    <div class="mt-4">
                        <a href="#" class="social-circle"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-circle"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-circle"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 footer-links">
                    <h5 class="mb-4 font-serif">O Palace</h5>
                    <ul>
                        <li><a href="#">Nossa História</a></li>
                        <li><a href="#">Galeria de Fotos</a></li>
                        <li><a href="#">Trabalhe Conosco</a></li>
                        <li><a href="#">Política de Privacidade</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 footer-links">
                    <h5 class="mb-4 font-serif">Serviços</h5>
                    <ul>
                        <li><a href="#">Café da Manhã</a></li>
                        <li><a href="#">Bodega de Vinhos</a></li>
                        <li><a href="#">Casamentos (Boda)</a></li>
                        <li><a href="#">Eventos Corporativos</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h5 class="mb-4 font-serif">Newsletter</h5>
                    <p class="text-secondary small mb-4">Receba convites exclusivos para nossas noites temáticas.</p>
                    <form class="position-relative">
                        <input type="email" class="form-control bg-transparent border-secondary text-white rounded-0 py-3" placeholder="Seu e-mail profissional">
                        <button class="btn btn-link position-absolute end-0 top-50 translate-middle-y gold-text pe-3">
                            <i class="fa fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="copyright text-center">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-md-start">
                        © {{ date('Y') }} SAX Palace. Todos os direitos reservados.
                    </div>
                    <div class="col-md-6 text-md-end">
                        Desenvolvido por SAX Full Service
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <script>
        // Inicialização de Animações
        AOS.init({
            duration: 1000,
            once: true,
            easing: 'ease-out-back'
        });

        // Efeito Header ao Rolas
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Controle do Swiper (Destaques)
        document.addEventListener('DOMContentLoaded', function() {
            if(document.querySelector('.mySwiper')) {
                const swiper = new Swiper(".mySwiper", {
                    effect: "fade",
                    loop: true,
                    autoplay: { delay: 5000 },
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev",
                    }
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>