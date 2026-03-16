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
