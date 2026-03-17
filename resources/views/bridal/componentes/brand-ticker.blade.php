{{-- SAX Bridal — Brand Ticker --}}
<div class="brand-ticker">
    <div class="ticker-track">
        @foreach($brands as $brand)
            <span class="ticker-item">
                @if(!empty($brand->image))
                    <img src="{{ asset('storage/' . $brand->image) }}"
                         alt="{{ $brand->name }}"
                         class="ticker-logo"
                         loading="lazy" decoding="async">
                @else
                    <span class="ticker-text">{{ $brand->name }}</span>
                @endif
            </span>
        @endforeach

        {{-- Duplicate for seamless infinite loop --}}
        @foreach($brands as $brand)
            <span class="ticker-item">
                @if(!empty($brand->image))
                    <img src="{{ asset('storage/' . $brand->image) }}"
                         alt="{{ $brand->name }}"
                         class="ticker-logo"
                         loading="lazy" decoding="async">
                @else
                    <span class="ticker-text">{{ $brand->name }}</span>
                @endif
            </span>
        @endforeach
    </div>
</div>

