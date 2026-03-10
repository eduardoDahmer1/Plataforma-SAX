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
