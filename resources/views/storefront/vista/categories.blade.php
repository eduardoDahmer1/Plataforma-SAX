@php
    $limit = (string) ($section['category_limit'] ?? 'all');
    $opticalItems = app(\App\Services\OpticalNavigationService::class)->items();
    if ($limit !== 'all') {
        $opticalItems = $opticalItems->take((int) $limit);
    }
    $count = $opticalItems->count();
@endphp

@if($opticalItems->isNotEmpty())
<section class="sax-category-strip vista-optical-categories py-5">
    <div class="container-fluid px-lg-5">
        @if(filled($sectionContent['title']) || filled($sectionContent['description']))
            <header class="sax-home-section-heading">
                @if(filled($sectionContent['title']))<h2>{{ $sectionContent['title'] }}</h2>@endif
                @if(filled($sectionContent['description']))<p>{{ $sectionContent['description'] }}</p>@endif
            </header>
        @endif
        <div class="category-wrapper vista-optical-categories__grid vista-optical-categories__grid--{{ min($count, 4) }}">
            @foreach($opticalItems as $item)
                <a href="{{ $item['url'] }}" class="category-item">
                    <span class="category-name">{{ $item['label'] }}</span>
                    <span class="category-img-box">
                        @if($item['photo'])
                            <img src="{{ $item['photo'] }}" alt="{{ $item['label'] }}" loading="lazy" decoding="async">
                        @else
                            <i class="fa-solid fa-glasses" aria-hidden="true"></i>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
