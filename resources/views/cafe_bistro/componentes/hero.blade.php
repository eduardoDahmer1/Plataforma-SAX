{{-- SAX Café & Bistrô — Hero --}}
<section class="hero-cafe" id="hero">

    {{-- Imagem de fundo dinâmica --}}
    @if($cafeBistro->hero_imagen)
        <img src="{{ asset('storage/'.$cafeBistro->hero_imagen) }}"
             class="hero-cafe-bg" alt="" fetchpriority="high" decoding="async">
    @endif

    {{-- Overlay sobre a imagem de fundo --}}
    <div class="hero-overlay"></div>

    <div class="container hero-content">
        <span class="eyebrow">SAX Café & Bistrô · PJC</span>

        <h1 class="hero-title" data-reveal="up">
            {{ $cafeBistro->hero_titulo ?? 'Um lugar para saborear o momento.' }}
        </h1>

        <p class="hero-subtitle" data-reveal="up">
            {{ $cafeBistro->hero_subtitulo ?? 'Frescor ao amanhecer, cafés de origem e jantares para recordar.' }}
        </p>

        <div class="hero-actions" data-reveal="up">
            <a href="{{ $cafeBistro->whatsapp_link }}" target="_blank" class="btn-cafe-primary">
                Reservar Mesa
            </a>
            <a href="#cardapio" class="btn-cafe-outline">
                Ver a Carta
            </a>
        </div>
    </div>

</section>
