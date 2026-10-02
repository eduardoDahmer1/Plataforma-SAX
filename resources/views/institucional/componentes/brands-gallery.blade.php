@php
    $gallery = sax_rotate_images($institucional->gallery_images ?: [], 1);
    $rotatedBrands = isset($brands) ? collect(sax_rotate_images($brands->all(), 1)) : collect();
@endphp
<section class="inst-gallery">
    <div class="container">
        <header class="inst-section-head inst-section-head--center" data-aos="fade-up">
            <div><p class="inst-kicker">{{ $copy('inst_gallery_eyebrow', 'Por dentro da SAX') }}</p><h2>{{ $copy('inst_gallery_title', 'Espaços que contam histórias') }}</h2></div>
        </header>
        @if($gallery)
            <div class="inst-gallery__grid" id="institucionalGalleryGrid">
                @foreach($gallery as $image)
                    <a href="{{ asset('storage/' . $image) }}" data-fancybox="gallery" class="inst-gallery__item inst-gallery__item--{{ ($loop->index % 5) + 1 }}" data-aos="fade-up">
                        <img src="{{ asset('storage/' . $image) }}" alt="Galeria SAX Department Store" loading="lazy">
                        <span><i class="fa-solid fa-expand"></i> Ver imagem</span>
                    </a>
                @endforeach
            </div>
        @endif
        @if($rotatedBrands->isNotEmpty())
            <div class="inst-brands" data-aos="fade-up">
                <span class="inst-brands__label">Marcas que fazem parte da nossa curadoria</span>
                <div class="swiper brandsSwiper">
                    <div class="swiper-wrapper">
                        @foreach($rotatedBrands as $brand)
                            <div class="swiper-slide"><img src="{{ asset('storage/' . $brand->image) }}" alt="{{ $brand->name }}" title="{{ $brand->name }}" loading="lazy"></div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
