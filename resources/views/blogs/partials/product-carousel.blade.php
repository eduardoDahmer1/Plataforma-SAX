<section class="blog-product-carousel" aria-label="{{ $title ?: 'Productos del artículo' }}">
    <div class="blog-product-carousel__heading">
        <h2>{{ $title ?: 'Productos del artículo' }}</h2>
        <div class="blog-product-carousel__controls">
            <button type="button" data-blog-carousel-prev aria-controls="{{ $carouselId }}" aria-label="Productos anteriores"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
            <button type="button" data-blog-carousel-next aria-controls="{{ $carouselId }}" aria-label="Productos siguientes"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="blog-product-carousel__track" id="{{ $carouselId }}" tabindex="0" role="region" aria-label="{{ $title ?: 'Productos del artículo' }}">
        @foreach($items as $item)
            @php
                $name = $item->name ?: $item->external_name;
                $translated = $item->translations->firstWhere('locale', translation_locale())?->name;
                if (translation_locale() !== 'pt-br' && filled($translated)
                    && (mb_strtolower(trim($translated)) !== mb_strtolower(trim((string) $item->external_name))
                        || mb_strtolower(trim((string) $name)) === mb_strtolower(trim((string) $item->external_name)))) {
                    $name = $translated;
                }
            @endphp
            <a class="blog-product-carousel__card product-card-standard" href="{{ route('produto.show', $item->slug ?: $item->id) }}">
                <img src="{{ $item->photo_url }}" alt="{{ $name }}" loading="lazy" decoding="async">
                <span class="product-card-standard__brand">{{ $item->brand?->name }}</span>
                <span class="product-card-standard__name">{{ $name }}</span>
                <span class="product-card-standard__price">{{ currency_format($item->price) }}</span>
            </a>
        @endforeach
    </div>
</section>
