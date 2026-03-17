{{-- SAX Bridal — Products Carousel (reutilizable) --}}
<section class="bridal-products-section section-padding">
    <div class="container-fluid px-lg-5">

        @if(!empty($sectionLabel) || !empty($sectionTitle))
            <div class="text-center mb-5">
                @if(!empty($sectionLabel))
                    <span class="title-gold">{{ $sectionLabel }}</span>
                @endif
                @if(!empty($sectionTitle))
                    <h2 class="section-title">{{ $sectionTitle }}</h2>
                @endif
            </div>
        @endif

        <div class="swiper" data-products-swiper>
            <div class="swiper-wrapper">
                @foreach($products as $product)
                    @include('home-components.product-card', [
                        'item'         => $product,
                        'showPrice'    => false,
                        'showFavorite' => false,
                    ])
                @endforeach
            </div>
        </div>

    </div>
</section>

