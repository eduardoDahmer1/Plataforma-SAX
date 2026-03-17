<section class="sv-editorial section-padding">
    <div class="container">

        <div class="text-center mb-5" data-reveal="up">
            <span class="title-gold">{!! $sectionLabel !!}</span>
            <h2 class="section-title">{!! $sectionTitle !!}</h2>
        </div>

        @foreach($services as $i => $service)
            <div class="sv-block {{ $i % 2 !== 0 ? 'sv-block--reverse' : '' }}" data-reveal="up" style="transition-delay: {{ $i * 0.08 }}s">

                {{-- Imagen --}}
                <div class="sv-img-col">
                    @if(!empty($service['image']))
                        <img src="{{ asset('storage/' . $service['image']) }}" alt="{{ $service['title'] }}" class="sv-img"
                             loading="lazy" decoding="async" >
                    @else
                        <div class="sv-img sv-img-placeholder">
                            <i class="fas fa-concierge-bell"></i>
                            <span>{{ $service['title'] }}</span>
                        </div>
                    @endif
                </div>

                {{-- Texto --}}
                <div class="sv-text-col">
                    <span class="sv-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}.</span>
                    <h3 class="sv-title">{{ $service['title'] }}</h3>
                    <p class="sv-desc">{{ $service['description'] }}</p>
                </div>

            </div>
        @endforeach

        @if(!empty($ctaLink) && !empty($ctaText))
            <div class="text-center mt-5" data-reveal="up">
                <a href="{{ $ctaLink }}" class="btn-sax">{{ $ctaText }}</a>
            </div>
        @endif

    </div>
</section>

