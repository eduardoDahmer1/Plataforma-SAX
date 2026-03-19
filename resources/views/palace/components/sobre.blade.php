<section id="sobre" class="py-5 py-lg-10">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 order-2 order-lg-1" data-aos="fade-up">
                <div class="pe-lg-5">
                    <h2 class="display-5 mb-4 font-serif">{{ $palace->hero_titulo }}</h2>
                    <p class="text-secondary fs-5 mb-4">{{ $palace->hero_descricao }}</p>
                    <div class="row g-4 pt-3">
                        <div class="col-6">
                            <div class="border-start border-gold border-3 ps-3">
                                <h3 class="h2 mb-0">1000+</h3>
                                <p class="small text-uppercase mb-0 text-secondary">Rótulos</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border-start border-gold border-3 ps-3">
                                <h3 class="h2 mb-0">Piso 11</h3>
                                <p class="small text-uppercase mb-0 text-secondary">Vista Prime</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-1 order-lg-2" data-aos="zoom-in">
                <div class="position-relative">
                    <img src="{{ $palace->hero_imagem ? asset('storage/' . $palace->hero_imagem) : 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b' }}" 
                         class="img-fluid shadow-lg rounded-3" alt="Palace Interior">
                    <div class="bg-gold position-absolute d-none d-md-block" style="width: 100px; height: 100px; bottom: -20px; right: -20px; z-index: -1;"></div>
                </div>
            </div>
        </div>
    </div>
</section>