@extends('layout.palace')

@section('content')
    <section class="hero-slider position-relative overflow-hidden">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="hero-zoom-wrapper">
                        <img src="{{ $palace->hero_imagem ? asset('storage/' . $palace->hero_imagem) : 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b' }}"
                            class="hero-img">
                    </div>
                    <div class="hero-overlay"></div>
                    <div class="container h-100 position-relative z-index-2">
                        <div class="row h-100 align-items-center justify-content-center">
                            <div class="col-lg-10 text-center" data-aos="fade-up">
                                <span class="text-gold letter-spacing-5 text-uppercase mb-3 d-block">Exclusive
                                    Experience</span>
                                <h1 class="display-1 font-serif text-white mb-4">{{ $palace->hero_titulo ?? 'SAX PALACE' }}
                                </h1>
                                <div class="gold-divider mx-auto mb-4"></div>
                                <p class="lead text-white-50 mx-auto mb-5 w-75 d-none d-md-block">
                                    {{ $palace->hero_descricao }}</p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}"
                                        class="btn-palace">Reservar Agora</a>
                                    <a href="#sobre" class="btn-palace bg-white text-dark border-white">Explorar
                                        Detalhes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="scroll-down">
            <span>EXPLORAR</span>
            <div class="line"></div>
        </div>
    </section>

    <section id="sobre" class="section-padding bg-palace-dark">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-5" data-aos="fade-right">
                    <span class="text-gold text-uppercase letter-spacing-2">Bem-vindo ao Extraordinário</span>
                    <h2 class="display-4 font-serif mt-3">{{ $palace->hero_titulo }}</h2>
                    <div class="gold-divider ms-0 mb-4" style="width: 80px;"></div>
                    <p class="text-secondary mb-4">
                        {{ $palace->hero_descricao }}
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
                        <img src="{{ $palace->hero_imagem ? asset('storage/' . $palace->hero_imagem) : 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b' }}"
                            class="img-fluid position-relative z-index-2 rounded shadow-24" alt="Interior SAX Palace"
                            style="min-height: 450px; width: 100%; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding bg-palace-soft">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 font-serif">{{ $palace->gastronomia_titulo ?? 'A Arte de Servir' }}</h2>
                <div class="gold-divider mx-auto"></div>
            </div>

            <div class="row g-0 overflow-hidden rounded-4 shadow-lg">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="food-box group">
                        <div class="food-img-wrap">
                            <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085"
                                class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="food-content">
                            <span class="gold-text fs-4 font-serif mb-2 d-block">01.</span>
                            <h3 class="font-serif text-white">Café da Manhã</h3>
                            <p class="small text-white-50">{{ $palace->gastronomia_cafe_desc }}</p>
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}"
                                class="btn-palace btn-sm mt-3">Ver Cardápio</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="food-box group active">
                        <div class="food-img-wrap">
                            <img src="https://images.unsplash.com/photo-1544025162-d76694265947"
                                class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="food-content">
                            <span class="gold-text fs-4 font-serif mb-2 d-block">02.</span>
                            <h3 class="font-serif text-white">Almoço Executivo</h3>
                            <p class="small text-white-50">{{ $palace->gastronomia_almoco_desc }}</p>
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}"
                                class="btn-palace btn-sm mt-3 border-white text-white">Ver Cardápio</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="food-box group">
                        <div class="food-img-wrap">
                            <img src="https://images.unsplash.com/photo-1559339352-11d035aa65de"
                                class="w-100 h-100 object-fit-cover">
                        </div>
                        <div class="food-content">
                            <span class="gold-text fs-4 font-serif mb-2 d-block">03.</span>
                            <h3 class="font-serif text-white">Jantar à la Carte</h3>
                            <p class="small text-white-50">{{ $palace->gastronomia_jantar_desc }}</p>
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}"
                                class="btn-palace btn-sm mt-3">Ver Cardápio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="noite-arabe-parallax position-relative section-padding"
        style="background: url('{{ $palace->tematica_imagem ? asset('storage/' . $palace->tematica_imagem) : '' }}') center/cover fixed;">
        <div class="hero-overlay" style="background: rgba(0,0,0,0.85)"></div>
        <div class="container position-relative z-index-2">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8" data-aos="zoom-in">
                    <span
                        class="text-gold letter-spacing-5 text-uppercase d-block mb-3">{{ $palace->tematica_tag ?? 'Experiência Temática' }}</span>
                    <h2 class="display-3 font-serif text-white">{{ $palace->tematica_titulo ?? 'Noite Árabe' }}</h2>
                    <p class="text-secondary mb-5">{{ $palace->tematica_descricao }}</p>
                    <div class="row g-4 justify-content-center mb-5">
                        <div class="col-md-4">
                            <div class="p-4 border border-gold-subtle h-100">
                                <h5 class="text-white">Buffet Livre</h5>
                                <p class="small text-secondary mb-0">Sabores do Oriente Médio</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 border border-gold-subtle h-100">
                                <h5 class="text-white">Show ao Vivo</h5>
                                <p class="small text-secondary mb-0">Dança do Ventre Profissional</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 border border-gold h-100">
                                <h5 class="gold-text">{{ $palace->tematica_preco ?? 'Consulte' }}</h5>
                                <p class="small text-secondary mb-0">Por Pessoa</p>
                            </div>
                        </div>
                    </div>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" target="_blank"
                        class="btn-palace shadow-lg">RESERVAR MESA NO WHATSAPP</a>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding bg-black">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <span class="gold-text text-uppercase letter-spacing-2">Sommelier Excellence</span>
                    <h2 class="display-4 font-serif text-white">{{ $palace->bar_titulo ?? 'Bar e Bodega' }}</h2>
                    <p class="text-secondary mt-4">
                        {{ $palace->bar_descricao }}
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
                            @if ($palace->bar_imagem_1)
                                <img src="{{ asset('storage/' . $palace->bar_imagem_1) }}"
                                    class="img-fluid rounded-3 mb-3 shadow" style="aspect-ratio: 1/1; object-fit: cover;">
                            @endif
                            @if ($palace->bar_imagem_2)
                                <img src="{{ asset('storage/' . $palace->bar_imagem_2) }}"
                                    class="img-fluid rounded-3 shadow" style="aspect-ratio: 1/1; object-fit: cover;">
                            @endif
                        </div>
                        <div class="col-6 pt-5">
                            @if ($palace->bar_imagem_3)
                                <img src="{{ asset('storage/' . $palace->bar_imagem_3) }}"
                                    class="img-fluid rounded-3 mb-3 shadow" style="aspect-ratio: 1/1; object-fit: cover;">
                            @endif
                            <img src="https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b"
                                class="img-fluid rounded-3 shadow" style="aspect-ratio: 1/1; object-fit: cover;">
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
                    <h2 class="display-4 font-serif">{{ $palace->eventos_titulo ?? 'Celebre Conosco' }}</h2>
                    <p class="text-secondary">{{ $palace->eventos_descricao }}</p>
                </div>
            </div>

            <div class="row g-4 justify-content-center">
                @php
                    // Decodifica o JSON da galeria para Array
                    $galeria = is_array($palace->eventos_galeria)
                        ? $palace->eventos_galeria
                        : json_decode($palace->eventos_galeria, true);
                @endphp

                @if (!empty($galeria))
                    @foreach ($galeria as $foto)
                        <div class="col-sm-6 col-lg-3" data-aos="zoom-in">
                            <div class="event-card">
                                <img src="{{ asset('storage/' . $foto) }}" class="event-img" alt="Evento Palace">
                                <div class="event-overlay">
                                    <h4 class="font-serif h5">SAX Palace</h4>
                                    <p class="small">Momentos Memoráveis</p>
                                    <hr class="border-gold w-25 mx-auto">
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}"
                                        class="btn-palace btn-sm py-2 px-3 border border-white text-white text-decoration-none">Solicitar
                                        Orçamento</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>

    <section class="section-padding bg-palace-dark">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-4 font-serif text-white mb-4">Onde Estamos</h2>
                    <p class="text-secondary fs-5 mb-5">{{ $palace->contato_endereco }}</p>

                    <div class="p-4 border border-gold-subtle rounded-3 mb-5">
                        <h5 class="text-gold mb-3">Horário de Atendimento</h5>
                        <ul class="list-unstyled text-white mb-0">
                            <li class="mb-2 d-flex justify-content-between"><span>Segunda:</span>
                                <span>{{ $palace->contato_horario_segunda }}</span></li>
                            <li class="mb-2 d-flex justify-content-between"><span>Terça a Sábado:</span>
                                <span>{{ $palace->contato_horario_sabado }}</span></li>
                            <li class="d-flex justify-content-between"><span>Domingo:</span>
                                <span>{{ $palace->contato_horario_domingo }}</span></li>
                        </ul>
                    </div>

                    <div class="d-flex align-items-center gap-3 text-white">
                        <i class="bi bi-whatsapp fs-2 text-gold"></i>
                        <div>
                            <small class="text-secondary d-block">RESERVAS WHATSAPP</small>
                            <span class="fs-4">{{ $palace->contato_whatsapp }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="rounded-4 overflow-hidden shadow-lg border border-dark" style="height: 450px;">
                        @if ($palace->contato_mapa_iframe)
                            {!! $palace->contato_mapa_iframe !!}
                        @else
                            <iframe src="https://www.google.com/maps/embed?..." width="100%" height="100%"
                                style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>


<style>
    /* Hero Animations */
    .hero-img {
        width: 100%;
        height: 100vh;
        object-fit: cover;
        transition: transform 10s ease-out;
    }

    .swiper-slide-active .hero-img {
        transform: scale(1.15);
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.8));
    }

    /* Food Boxes */
    .food-box {
        position: relative;
        height: 500px;
        overflow: hidden;
        cursor: pointer;
    }

    .food-img-wrap {
        width: 100%;
        height: 100%;
        transition: 0.8s;
    }

    .food-content {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 40px;
        background: linear-gradient(to top, #000 30%, transparent);
        z-index: 2;
        transition: 0.5s;
    }

    .food-box:hover .food-img-wrap {
        transform: scale(1.1);
        filter: brightness(0.5);
    }

    .food-box:hover .food-content {
        padding-bottom: 60px;
    }

    /* Event Cards */
    .event-card {
        position: relative;
        height: 400px;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    .event-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: 0.5s;
    }

    .event-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 30px;
        opacity: 0;
        transition: 0.5s;
    }

    .event-card:hover .event-overlay {
        opacity: 1;
    }

    .event-card:hover .event-img {
        transform: scale(1.1);
        filter: blur(2px);
    }

    /* General Helpers */
    .icon-circle-gold {
        width: 50px;
        height: 50px;
        border: 1px solid var(--palace-gold);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--palace-gold);
        flex-shrink: 0;
    }

    .scroll-down {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        color: #fff;
        text-align: center;
    }

    .scroll-down .line {
        width: 1px;
        height: 50px;
        background: var(--palace-gold);
        margin: 10px auto;
        animation: heightGrow 2s infinite;
    }

    @keyframes heightGrow {
        0% {
            height: 0;
            opacity: 0;
        }

        50% {
            height: 50px;
            opacity: 1;
        }

        100% {
            height: 0;
            opacity: 0;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    // Inicialização do Swiper com Efeito Fade
    const heroSwiper = new Swiper(".mySwiper", {
        effect: "fade",
        speed: 1500,
        autoplay: {
            delay: 6000,
            disableOnInteraction: false
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true
        },
        loop: true
    });
</script>
@endpush
