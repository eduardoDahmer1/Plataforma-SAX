@if (isset($brands) && $brands->count() > 0)
    <section class="sax-brands-section">
        <div class="sax-brands-mobile">
            @if(filled($sectionContent['title']) || filled($sectionContent['description']))
                <div class="sax-brands-mobile__header">
                    <div>
                        <span>SAX Selection</span>
                        @if(filled($sectionContent['title']))<h2>{{ $sectionContent['title'] }}</h2>@endif
                        @if(filled($sectionContent['description']))<p class="sax-brand-description">{{ $sectionContent['description'] }}</p>@endif
                    </div>
                    <a href="{{ route('brands.index') }}" aria-label="{{ __('messages.nossas_marcas') }}">
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            @endif

            <div class="sax-brands-mobile__rail">
                @foreach ($brands as $brand)
                    @php
                        $rawBrandBanner = trim((string) ($brand->banner ?? ''));
                        if (preg_match('/^https?:\/\//i', $rawBrandBanner)) {
                            $brandBannerUrl = $rawBrandBanner;
                        } elseif ($rawBrandBanner !== '') {
                            $cleanBrandBanner = preg_replace('#^storage/#i', '', ltrim($rawBrandBanner, '/'));
                            $brandBannerUrl = asset('storage/' . $cleanBrandBanner);
                        } else {
                            $brandBannerUrl = asset('storage/uploads/banner_horizontal.webp');
                        }
                    @endphp
                    <a href="{{ url('marcas/' . ($brand->slug ?: $brand->id)) }}" class="sax-brand-mobile-card">
                        <span class="sax-brand-mobile-card__image">
                            <img src="{{ $brandBannerUrl }}" alt="{{ $brand->name }}" loading="lazy" decoding="async" onerror="this.src='{{ asset('storage/uploads/banner_horizontal.webp') }}'">
                        </span>
                        <span class="sax-brand-mobile-card__meta">
                            <strong>{{ $brand->name }}</strong>
                            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="sax-carousel-master sax-brands-desktop">
            @if(filled($sectionContent['title']) || filled($sectionContent['description']))
                <header class="sax-brand-heading">
                    <span class="sax-brand-heading__eyebrow">SAX Selection</span>
                    @if(filled($sectionContent['title']))<h2 class="sax-main-title">{{ $sectionContent['title'] }}</h2>@endif
                    @if(filled($sectionContent['description']))<p class="sax-brand-description">{{ $sectionContent['description'] }}</p>@endif
                    <a href="{{ route('brands.index') }}" class="sax-brand-heading__link">
                        {{ __('messages.nossas_marcas') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </header>
            @endif
            <div class="sax-carousel-3d" 
                 id="brandsCarousel" 
                 data-storage-base="{{ asset('storage') }}" 
                 data-marcas-url="{{ url('marcas') }}"
                 data-fallback-banner="{{ asset('storage/uploads/banner_horizontal.webp') }}">
            </div>

            <div class="sax-carousel-footer">
                <div id="saxBrandName" class="sax-brand-label"></div>

                <div class="sax-controls">
                    <button type="button" id="saxPrev" class="sax-nav-btn" aria-label="Marca anterior"><i class="fa-solid fa-arrow-left"></i></button>
                    <div class="sax-indicators" id="saxDots"></div>
                    <button type="button" id="saxNext" class="sax-nav-btn" aria-label="Próxima marca"><i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>
        </div>
    </section>

    <script>
        window.saxBrandsData = {!! $brands->toJson() !!};
    </script>
@endif
