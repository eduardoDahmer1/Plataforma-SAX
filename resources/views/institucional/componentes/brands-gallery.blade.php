@php
    // Garante que a galeria de imagens seja interpretada como um array válido de forma segura
    $gallery = is_array($institucional->gallery_images) 
        ? $institucional->gallery_images 
        : json_decode($institucional->gallery_images, true);
@endphp

<section class="py-5 bg-white overflow-hidden">
    <div class="container py-5">
        
        {{-- CAROUSEL DE MARCAS --}}
        <div class="brands-header mb-5" data-aos="fade-up">
            <h2 class="section-title-elegant text-center">
                {{ __('messages.brands_title') ?? 'Grandes Marcas' }}
            </h2>
            <div class="title-divider mx-auto"></div>
        </div>
        
        <div class="swiper brandsSwiper mb-5" data-aos="fade-in">
            <div class="swiper-wrapper align-items-center">
                @if(isset($brands) && count($brands) > 0)
                    @foreach($brands as $brand)
                        <div class="swiper-slide text-center">
                            <img src="{{ asset('storage/' . $brand->image) }}" 
                                 class="brand-logo-img" 
                                 alt="{{ $brand->name }}" 
                                 title="{{ $brand->name }}">
                        </div>
                    @endforeach
                @else
                    {{-- Fallback conceitual em caso de ausência de marcas no banco --}}
                    <div class="swiper-slide text-center opacity-50">
                        <span class="x-small fw-bold tracking-wider text-muted">SAX Premium Brand</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- GALERIA INSTITUCIONAL --}}
        <div class="gallery-header mt-5 pt-5 mb-4" data-aos="fade-up">
            <h2 class="section-title-elegant text-center">
                {{ __('messages.gallery_title') ?? 'Nossa Galeria' }}
            </h2>
            <div class="title-divider mx-auto"></div>
        </div>

        <div class="row g-3">
            @if(!empty($gallery))
                @foreach($gallery as $image)
                    <div class="col-6 col-md-3" data-aos="fade-up">
                        <a href="{{ asset('storage/' . $image) }}" 
                           data-fancybox="gallery" 
                           data-caption="{{ __('messages.gallery_caption_text') ?? 'SAX Department Store - Detalhes Exclusivos' }}"
                           class="gallery-card">
                            <div class="gallery-overlay">
                                <i class="bi bi-fullscreen"></i>
                                <span class="overlay-text">
                                    {{ __('messages.gallery_view_details') ?? 'Ver Detalhes' }}
                                </span>
                            </div>
                            <img src="{{ asset('storage/' . $image) }}" class="img-fluid" alt="Gallery">
                        </a>
                    </div>
                @endforeach
            @else
                {{-- Fallback elegante para manter o design caso não existam imagens cadastradas --}}
                @for($i = 1; $i <= 4; $i++)
                    <div class="col-6 col-md-3" data-aos="fade-up">
                        <div class="gallery-card border bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <span class="text-muted x-small italic">SAX Space {{ $i }}</span>
                        </div>
                    </div>
                @endfor
            @endif
        </div>
        
    </div>
</section>