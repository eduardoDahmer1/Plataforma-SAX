<section class="palace-hero palace-section" id="inicio">
    <div class="palace-hero__media">
        <img src="{{ $palace->hero_imagem ? asset('storage/' . $palace->hero_imagem) : 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b' }}"
             class="palace-hero__image" alt="Hero" loading="eager">
        <div class="palace-hero__overlay"></div>
    </div>

    <div class="container position-relative palace-hero__container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7 col-xl-6 palace-reveal" data-aos="fade-right">
                <span class="palace-eyebrow text-uppercase">{{ __('messages.seccion_principal_badge') }}</span>
                <h1 class="palace-hero__title">{{ $t->palace_hero_titulo ?? $palace->hero_titulo }}</h1>
                <p class="palace-hero__lead d-none d-md-block">{{ $t->palace_hero_descricao ?? $palace->hero_descricao }}</p>

                <div class="d-flex flex-wrap gap-3 palace-hero__actions">
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" class="btn palace-btn palace-btn--gold btn-lg px-4 px-md-5 py-3 text-uppercase fw-bold">
                        {{ __('messages.reservar_btn') ?? 'Reservar' }}
                    </a>
                    <a href="#sobre" class="btn palace-btn palace-btn--ghost btn-lg px-4 px-md-5 py-3 text-uppercase fw-bold">
                        {{ __('messages.descobrir_btn') ?? 'Descobrir' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>