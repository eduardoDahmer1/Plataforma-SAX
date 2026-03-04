{{-- SAX Bridal — Products Carousel (reutilizable) --}}
<section class="bridal-products-section section-padding">
    <div class="container">

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
                    <div class="swiper-slide">
                        <div class="bp-card" data-reveal="up">
                            <div class="bp-img-wrap">
                                <img src="{{ $product->photo_url }}"
                                     alt="{{ $product->name }}"
                                     class="bp-img"
                                     loading="lazy"
                                     decoding="async">
                            </div>
                            <div class="bp-body">
                                <span class="bp-brand">{{ $product->brand->name ?? '' }}</span>
                                <p class="bp-name">{{ $product->name }}</p>
                                <a href="#" class="bp-btn">Detalhes</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="products-swiper-pagination swiper-pagination mt-4"></div>

    </div>
</section>

@push('styles')
<style>
    .bridal-products-section {
        background: var(--bridal-white);
    }

    .bp-card {
        background: var(--bridal-cream);
        border-radius: 2px;
        overflow: hidden;
    }

    @media (hover: hover) {
        .bp-card {
            transition: transform 0.4s ease;
        }
        .bp-card:hover {
            transform: translateY(-6px);
        }
    }

    .bp-img-wrap {
        overflow: hidden;
        aspect-ratio: 3 / 4;
    }

    .bp-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 1.2s ease;
    }

    @media (hover: hover) {
        .bp-card:hover .bp-img {
            transform: scale(1.06);
        }
    }

    .bp-body {
        padding: 1.25rem 1rem 1.5rem;
        text-align: center;
    }

    .bp-brand {
        display: block;
        font-family: var(--font-sans);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--bridal-dark);
        margin-bottom: 0.4rem;
    }

    .bp-name {
        font-size: 0.72rem;
        text-transform: uppercase;
        color: #888;
        line-height: 1.5;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .bp-btn {
        display: inline-block;
        width: 100%;
        padding: 0.65rem 1rem;
        background: var(--bridal-gold);
        color: #fff;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .bp-btn:hover {
        background: var(--bridal-dark);
        color: #fff;
    }

    .products-swiper-pagination .swiper-pagination-bullet {
        background: var(--bridal-gold);
        opacity: 0.3;
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 0.25rem;
        transition: all 0.35s ease;
    }

    .products-swiper-pagination .swiper-pagination-bullet-active {
        opacity: 1;
        width: 1.75rem;
        border-radius: 0.25rem;
    }
</style>
@endpush
