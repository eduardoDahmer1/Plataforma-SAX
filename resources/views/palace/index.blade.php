@extends('layout.palace')

@section('content')
<section class="hero-slider position-relative overflow-hidden">
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            @forelse($conteudos as $item)
                <div class="swiper-slide">
                    <div class="hero-zoom-wrapper">
                        <img src="{{ asset('storage/' . $item->slider_principal) }}" class="hero-img">
                    </div>
                    <div class="hero-overlay"></div>
                    <div class="container h-100 position-relative z-index-2">
                        <div class="row h-100 align-items-center justify-content-center">
                            <div class="col-lg-10 text-center" data-aos="fade-up">
                                <span class="text-gold letter-spacing-5 text-uppercase mb-3 d-block">Exclusive Experience</span>
                                <h1 class="display-1 font-serif text-white mb-4">{{ $item->titulo }}</h1>
                                <div class="gold-divider mx-auto mb-4"></div>
                                <p class="lead text-white-50 mx-auto mb-5 w-75 d-none d-md-block">{{ $item->descricao }}</p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="#reservas" class="btn-palace">Reservar Agora</a>
                                    <a href="{{ route('palace.show', $item->id) }}" class="btn-palace bg-white text-dark border-white">Explorar Detalhes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="swiper-slide">
                    <img src="https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b" class="hero-img">
                    <div class="hero-overlay"></div>
                    <div class="container h-100 align-items-center d-flex justify-content-center">
                        <div class="text-center position-relative z-index-2">
                            <h1 class="display-1 font-serif text-white">SAX PALACE</h1>
                            <div class="gold-divider mx-auto"></div>
                            <p class="letter-spacing-5 text-gold text-uppercase">The Pinnacle of Luxury in CDE</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="swiper-pagination"></div>
    </div>
    
    <div class="scroll-down">
        <span>EXPLORAR</span>
        <div class="line"></div>
    </div>
</section>

<section class="section-padding bg-palace-dark">
    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="col-lg-5" data-aos="fade-right">
                <span class="text-gold text-uppercase letter-spacing-2">Bem-vindo ao Extraordinário</span>
                <h2 class="display-4 font-serif mt-3">Onde a sofisticação encontra o paladar</h2>
                <div class="gold-divider ms-0 mb-4" style="width: 80px;"></div>
                <p class="text-secondary mb-4">
                    Localizado no 11º andar da icônica SAX Department Store, o Palace oferece uma vista panorâmica 
                    incomparável e uma curadoria gastronômica que atravessa continentes. Do café da manhã artesanal 
                    aos jantares de gala, cada detalhe é uma celebração ao luxo.
                </p>
                <div class="row g-4 mb-5">
                    <div class="col-6">
                        <h4 class="font-serif gold-text mb-0">1000+</h4>
                        <small class="text-uppercase text-secondary">Rótulos de Vinhos</small>
                    </div>
                    <div class="col-6">
                        <h4 class="font-serif gold-text mb-0">Piso 11</h4>
                        <small class="text-uppercase text-secondary">Localização Prime</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="position-relative p-5">
                    <div class="img-border-box"></div>
                    <img src="https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?q=80&w=1200&auto=format&fit=crop" 
                        class="img-fluid position-relative z-index-2 rounded shadow-24" 
                        alt="Interior SAX Palace" 
                        style="min-height: 450px; width: 100%; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-palace-soft">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 font-serif">A Arte de Servir</h2>
            <div class="gold-divider mx-auto"></div>
        </div>
        
        <div class="row g-0 overflow-hidden rounded-4 shadow-lg">
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="food-box group">
                    <div class="food-img-wrap">
                        <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="food-content">
                        <span class="gold-text fs-4 font-serif mb-2 d-block">01.</span>
                        <h3 class="font-serif text-white">Café da Manhã</h3>
                        <p class="small opacity-0 group-hover-opacity-100 transition-all duration-500">
                            Pães artesanais, frutas selecionadas e a melhor torrefação de café da região.
                        </p>
                        <a href="#" class="btn-palace btn-sm mt-3">Ver Cardápio</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="food-box group active">
                    <div class="food-img-wrap">
                        <img src="https://images.unsplash.com/photo-1544025162-d76694265947" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="food-content">
                        <span class="gold-text fs-4 font-serif mb-2 d-block">02.</span>
                        <h3 class="font-serif text-white">Almoço Executivo</h3>
                        <p class="small">A elegância que o seu almoço de negócios exige, com pratos contemporâneos.</p>
                        <a href="#" class="btn-palace btn-sm mt-3 border-white text-white">Ver Cardápio</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="food-box group">
                    <div class="food-img-wrap">
                        <img src="https://images.unsplash.com/photo-1559339352-11d035aa65de" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="food-content">
                        <span class="gold-text fs-4 font-serif mb-2 d-block">03.</span>
                        <h3 class="font-serif text-white">Jantar à la Carte</h3>
                        <p class="small opacity-0 group-hover-opacity-100">Uma experiência sensorial sob a luz de velas e vista para a Ponte da Amizade.</p>
                        <a href="#" class="btn-palace btn-sm mt-3">Ver Cardápio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="noite-arabe-parallax position-relative section-padding" style="background: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4') center/cover fixed;">
    <div class="hero-overlay" style="background: rgba(0,0,0,0.85)"></div>
    <div class="container position-relative z-index-2">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8" data-aos="zoom-in">
                <img src="{{ asset('images/icons/arabian-lamp.png') }}" alt="" width="60" class="mb-4 filter-gold">
                <h2 class="display-3 font-serif text-white">NOITE ÁRABE</h2>
                <p class="letter-spacing-5 text-gold mb-5">TODAS AS SEXTAS-FEIRAS | 20:00H</p>
                <div class="row g-4 justify-content-center mb-5">
                    <div class="col-md-4">
                        <div class="p-4 border border-gold-subtle">
                            <h5 class="text-white">Buffet Livre</h5>
                            <p class="small text-secondary mb-0">Sabores do Oriente Médio</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 border border-gold-subtle">
                            <h5 class="text-white">Show ao Vivo</h5>
                            <p class="small text-secondary mb-0">Dança do Ventre Profissional</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 border border-gold">
                            <h5 class="gold-text">24 U$</h5>
                            <p class="small text-secondary mb-0">Por Pessoa</p>
                        </div>
                    </div>
                </div>
                <a href="https://wa.me/595981528186" target="_blank" class="btn-palace shadow-lg">RESERVAR MESA NO WHATSAPP</a>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-black">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <span class="gold-text text-uppercase letter-spacing-2">Sommelier Excellence</span>
                <h2 class="display-4 font-serif text-white">Bar e Bodega</h2>
                <p class="text-secondary mt-4">
                    Nossa bodega abriga as mais prestigiadas etiquetas mundiais. 
                    De raridades como <strong>Johnnie Walker Blue Label</strong> e <strong>The Dalmore</strong> 
                    até uma curadoria de Champagnes Moët & Chandon.
                </p>
                <div class="mt-5">
                    <div class="d-flex mb-4">
                        <div class="icon-circle-gold me-3"><i class="bi bi-pentagon-fill"></i></div>
                        <div>
                            <h5 class="text-white mb-1">Vinhos Selecionados</h5>
                            <p class="small text-secondary">América do Sul, Europa e Velho Mundo.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-4">
                        <div class="icon-circle-gold me-3"><i class="bi bi-cup-straw"></i></div>
                        <div>
                            <h5 class="text-white mb-1">Mixologia Autoral</h5>
                            <p class="small text-secondary">Drinks exclusivos preparados por barmans premiados.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="row g-3">
                    <div class="col-6">
                        <img src="https://images.unsplash.com/photo-1510812431401-41d2bd2722f3" class="img-fluid rounded-3 mb-3" alt="Vinho">
                        <img src="https://images.unsplash.com/photo-1470337458703-46ad1756a187" class="img-fluid rounded-3" alt="Drink">
                    </div>
                    <div class="col-6 pt-5">
                        <img src="https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?q=80&w=800&auto=format&fit=crop" 
                            class="img-fluid rounded-3 mb-3 shadow" 
                            alt="Bodega SAX Palace" 
                            style="aspect-ratio: 1/1; object-fit: cover;">
                        <img src="https://images.unsplash.com/photo-1551024709-8f23befc6f87?q=80&w=800&auto=format&fit=crop" 
                            class="img-fluid rounded-3 shadow" 
                            alt="Whisky SAX Palace" 
                            style="aspect-ratio: 1/1; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-palace-soft border-top border-dark py-5">
    <div class="container">
        <div class="row justify-content-center mb-5" data-aos="fade-up">
            <div class="col-lg-8 text-center">
                <h2 class="display-4 font-serif">Celebre Conosco</h2>
                <p class="text-secondary">Transformamos seus sonhos em eventos memoráveis, com infraestrutura completa e buffet personalizado.</p>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-sm-6 col-lg-3" data-aos="zoom-in" data-aos-delay="100">
                <div class="event-card">
                    <img src="https://images.unsplash.com/photo-1519741497674-611481863552" class="event-img" alt="Casamentos">
                    <div class="event-overlay">
                        <h4 class="font-serif h5">Boda (Casamentos)</h4>
                        <p class="small">Decorações românticas exclusivas sem custo adicional.</p>
                        <hr class="border-gold w-25 mx-auto">
                        <a href="#" class="btn-palace btn-sm py-2 px-3 border border-white text-white text-decoration-none">Solicitar Orçamento</a>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3" data-aos="zoom-in" data-aos-delay="200">
                <div class="event-card">
                    <img src="https://images.unsplash.com/photo-1511578314322-379afb476865" class="event-img" alt="Corporativo">
                    <div class="event-overlay">
                        <h4 class="font-serif h5">Corporativo</h4>
                        <p class="small">Lançamentos de marcas e confraternizações em alto estilo.</p>
                        <hr class="border-gold w-25 mx-auto">
                        <a href="#" class="btn-palace btn-sm py-2 px-3 border border-white text-white text-decoration-none">Ver Espaços</a>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3" data-aos="zoom-in" data-aos-delay="300">
                <div class="event-card">
                    <img src="https://images.unsplash.com/photo-1513151233558-d860c5398176" class="event-img" alt="Celebrações">
                    <div class="event-overlay">
                        <h4 class="font-serif h5">Datas Especiais</h4>
                        <p class="small">Aniversários, noivados e celebrações íntimas.</p>
                        <hr class="border-gold w-25 mx-auto">
                        <a href="#" class="btn-palace btn-sm py-2 px-3 border border-white text-white text-decoration-none">Reservar Data</a>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3" data-aos="zoom-in" data-aos-delay="400">
                <div class="event-card">
                    <img src="https://images.unsplash.com/photo-1549417229-aa67d3263c09" class="event-img" alt="15 Anos">
                    <div class="event-overlay">
                        <h4 class="font-serif h5">Debutantes</h4>
                        <p class="small">O brilho e a magia dos 15 anos em uma festa inesquecível.</p>
                        <hr class="border-gold w-25 mx-auto">
                        <a href="#" class="btn-palace btn-sm py-2 px-3 border border-white text-white text-decoration-none">Saiba Mais</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Hero Animations */
    .hero-img { 
        width: 100%; height: 100vh; object-fit: cover; 
        transition: transform 10s ease-out;
    }
    .swiper-slide-active .hero-img { transform: scale(1.15); }
    .hero-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.8));
    }
    
    /* Food Boxes */
    .food-box {
        position: relative; height: 500px; overflow: hidden;
        cursor: pointer;
    }
    .food-img-wrap { width: 100%; height: 100%; transition: 0.8s; }
    .food-content {
        position: absolute; bottom: 0; left: 0; width: 100%;
        padding: 40px; background: linear-gradient(to top, #000 30%, transparent);
        z-index: 2; transition: 0.5s;
    }
    .food-box:hover .food-img-wrap { transform: scale(1.1); filter: brightness(0.5); }
    .food-box:hover .food-content { padding-bottom: 60px; }

    /* Event Cards */
    .event-card {
        position: relative; height: 400px; border-radius: 15px;
        overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }
    .event-img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
    .event-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.7); display: flex; flex-direction: column;
        justify-content: center; align-items: center; padding: 30px;
        opacity: 0; transition: 0.5s;
    }
    .event-card:hover .event-overlay { opacity: 1; }
    .event-card:hover .event-img { transform: scale(1.1); filter: blur(2px); }

    /* General Helpers */
    .icon-circle-gold {
        width: 50px; height: 50px; border: 1px solid var(--palace-gold);
        border-radius: 50%; display: flex; align-items: center;
        justify-content: center; color: var(--palace-gold); flex-shrink: 0;
    }
    .scroll-down {
        position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%);
        z-index: 10; color: #fff; text-align: center;
    }
    .scroll-down .line {
        width: 1px; height: 50px; background: var(--palace-gold);
        margin: 10px auto; animation: heightGrow 2s infinite;
    }
    @keyframes heightGrow {
        0% { height: 0; opacity: 0; }
        50% { height: 50px; opacity: 1; }
        100% { height: 0; opacity: 0; }
    }
</style>
@endsection

@push('scripts')
<script>
    // Inicialização do Swiper com Efeito Fade
    const heroSwiper = new Swiper(".mySwiper", {
        effect: "fade",
        speed: 1500,
        autoplay: { delay: 6000, disableOnInteraction: false },
        pagination: { el: ".swiper-pagination", clickable: true },
        loop: true
    });
</script>
@endpush