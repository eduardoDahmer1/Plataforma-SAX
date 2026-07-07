<section id="sobre" class="palace-section palace-section--soft">
    <div class="container py-5 py-lg-6">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 order-2 order-lg-1 palace-reveal" data-aos="fade-up">
                <div class="palace-copy pe-lg-4">
                    <span class="palace-eyebrow text-uppercase">{{ __('messages.localizacao_label') }}</span>
                    <h2 class="palace-section__title mb-4">{{ $t->palace_hero_titulo ?? $palace->hero_titulo }}</h2>
                    <p class="palace-section__lead">{{ $t->palace_hero_descricao ?? $palace->hero_descricao }}</p>

                    <div class="row g-3 g-xl-4 pt-2 pt-md-4 palace-story-grid">
                        <div class="col-6">
                            <div class="palace-stat-card">
                                <span class="palace-stat-card__value">1000+</span>
                                <span class="palace-stat-card__label">{{ __('messages.rotulos_label') }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="palace-stat-card">
                                <span class="palace-stat-card__value">{{ __('messages.piso_label') }}</span>
                                <span class="palace-stat-card__label">{{ __('messages.vista_prime_label') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-1 order-lg-2 palace-reveal" data-aos="zoom-in">
                <div class="palace-image-frame">
                    <img src="{{ $palace->hero_imagem ? asset('storage/' . $palace->hero_imagem) : 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b' }}"
                         class="palace-image-frame__img" alt="Palace Interior" loading="lazy">
                    <div class="palace-image-frame__accent d-none d-md-block"></div>
                </div>
            </div>
        </div>
    </div>
</section>