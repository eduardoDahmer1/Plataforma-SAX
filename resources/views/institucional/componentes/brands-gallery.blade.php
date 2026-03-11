<section class="py-5 bg-white overflow-hidden">
    <div class="container py-5">
        <div class="brands-header mb-5" data-aos="fade-up">
            <h2 class="section-title-elegant text-center">Grandes Marcas</h2>
            <div class="title-divider mx-auto"></div>
        </div>
        
        <div class="swiper brandsSwiper mb-5" data-aos="fade-in">
            <div class="swiper-wrapper align-items-center">
                {{-- Novo loop usando a tabela de marcas --}}
                @foreach($brands as $brand)
                <div class="swiper-slide text-center">
                    {{-- Usando a coluna 'image' da tabela 'brands' --}}
                    <img src="{{ asset('storage/' . $brand->image) }}" 
                         class="brand-logo-img" 
                         alt="{{ $brand->name }}" 
                         title="{{ $brand->name }}">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Galeria permanece igual ou conforme sua necessidade --}}
        <div class="gallery-header mt-5 pt-5 mb-4" data-aos="fade-up">
            <h2 class="section-title-elegant text-center">Nossa Galeria</h2>
            <div class="title-divider mx-auto"></div>
        </div>

        <div class="row g-3">
            @foreach($institucional->gallery_images as $image)
            <div class="col-6 col-md-3" data-aos="fade-up">
                <a href="{{ asset('storage/' . $image) }}" 
                   data-fancybox="gallery" 
                   data-caption="SAX Department Store - Detalhes Exclusivos"
                   class="gallery-card">
                    <div class="gallery-overlay">
                        <i class="bi bi-fullscreen"></i>
                        <span class="overlay-text">Ver Detalhes</span>
                    </div>
                    <img src="{{ asset('storage/' . $image) }}" class="img-fluid" alt="Gallery">
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<style>
/* Títulos e Divisores */
.section-title-elegant {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    font-size: 2.2rem;
    color: #1a1a1a;
    letter-spacing: 1px;
}

.title-divider {
    width: 50px;
    height: 2px;
    background: #c5a059;
    margin-top: 15px;
}

/* Logos das Marcas */
.brand-logo-img {
    max-height: 45px;
    width: auto;
    filter: grayscale(100%);
    transition: all 0.4s ease;
    opacity: 0.5;
}

.brand-logo-img:hover {
    filter: grayscale(0%);
    opacity: 1;
    transform: scale(1.05);
}

/* Galeria Moderna */
.gallery-card {
    position: relative;
    display: block;
    height: 300px;
    overflow: hidden;
    background: #f8f8f8;
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.03);
}

.gallery-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s cubic-bezier(0.2, 0, 0.2, 1);
}

/* Overlay da Galeria */
.gallery-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    z-index: 2;
    transition: all 0.4s ease;
    backdrop-filter: blur(2px);
}

.gallery-overlay i {
    color: #c5a059;
    font-size: 1.5rem;
    margin-bottom: 8px;
    transform: translateY(20px);
    transition: transform 0.4s ease;
}

.overlay-text {
    color: #fff;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-family: 'Montserrat', sans-serif;
    transform: translateY(20px);
    transition: transform 0.4s ease 0.1s;
}

.gallery-card:hover img {
    transform: scale(1.1);
}

.gallery-card:hover .gallery-overlay {
    opacity: 1;
}

.gallery-card:hover .gallery-overlay i,
.gallery-card:hover .overlay-text {
    transform: translateY(0);
}

/* Responsivo */
@media (max-width: 768px) {
    .gallery-card { height: 200px; }
    .section-title-elegant { font-size: 1.8rem; }
    .overlay-text { font-size: 0.55rem; }
}
</style>