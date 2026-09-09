@if ($products->isNotEmpty())
    <section class="sax-section-container py-4">
        <div class="container-fluid px-lg-5">
            @if(filled($sectionContent['title']) || filled($sectionContent['description']))
                <header class="sax-product-section-heading">
                    @if(filled($sectionContent['title']))<h2 class="sax-section-title">{{ $sectionContent['title'] }}</h2>@endif
                    @if(filled($sectionContent['description']))<p class="sax-section-description">{{ $sectionContent['description'] }}</p>@endif
                </header>
            @endif
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    @foreach ($products as $item)
                        @include('home-components.product-card', ['item' => $item])
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
