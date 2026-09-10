@if($products->isNotEmpty())
<section class="vista-product-section" data-vista-product-carousel>
    @if(filled($sectionContent['title']) || filled($sectionContent['description']))
        <header class="vista-product-section__heading">
            @if(filled($sectionContent['title']))<h2>{{ $sectionContent['title'] }}</h2>@endif
            @if(filled($sectionContent['description']))<p>{{ $sectionContent['description'] }}</p>@endif
        </header>
    @endif

    <div class="vista-product-section__carousel">
        <div class="swiper vista-product-swiper">
            <div class="swiper-wrapper">
                @foreach($products as $item)
                    @include('home-components.product-card', ['item' => $item])
                @endforeach
            </div>
        </div>
        @if($products->count() > 1)
            <button type="button" class="vista-product-arrow vista-product-arrow--prev" aria-label="Productos anteriores"><i class="fa-solid fa-chevron-left"></i></button>
            <button type="button" class="vista-product-arrow vista-product-arrow--next" aria-label="Siguientes productos"><i class="fa-solid fa-chevron-right"></i></button>
        @endif
    </div>
</section>
@endif
