<section class="min-vh-100 d-flex align-items-center position-relative overflow-hidden bg-dark">
    <div class="position-absolute top-0 start-0 w-100 h-100 z-0">
        <img src="{{ $palace->hero_imagem ? asset('storage/' . $palace->hero_imagem) : 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b' }}" 
             class="w-100 h-100 object-fit-cover opacity-50" alt="Hero">
    </div>
    
    <div class="container position-relative z-1 text-white py-5">
        <div class="row">
            <div class="col-lg-8 col-xl-7" data-aos="fade-right">
                <span class="text-gold fw-bold text-uppercase tracking-widest mb-3 d-block">Experiência Exclusiva</span>
                <h1 class="display-2 fw-light mb-4">{{ $palace->hero_titulo ?? 'SAX PALACE' }}</h1>
                <p class="lead mb-5 opacity-75 d-none d-md-block">{{ $palace->hero_descricao }}</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" class="btn btn-gold btn-lg px-5 py-3 rounded-0 text-uppercase fw-bold">Reservar</a>
                    <a href="#sobre" class="btn btn-outline-light btn-lg px-5 py-3 rounded-0 text-uppercase fw-bold">Descobrir</a>
                </div>
            </div>
        </div>
    </div>
</section>