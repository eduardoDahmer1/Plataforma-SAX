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

@push('styles')
<style>
    .brand-ticker {
        background: #ffffff;
        padding: 1rem 0;
        border-top: 1px solid #f0ece6;
        border-bottom: 1px solid #f0ece6;
        overflow: hidden;
        display: flex;
    }

    .ticker-track {
        display: flex;
        align-items: center;
        white-space: nowrap;
        animation: tickerScroll 40s linear infinite;
    }

    .ticker-item {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 2.5rem;
    }

    .ticker-text {
        font-family: var(--font-display);
        font-size: 0.72rem;
        letter-spacing: 4px;
        color: var(--bridal-gold);
        font-weight: 400;
    }

    .ticker-logo {
        max-height: 4rem;
        width: auto;
        max-width: 12rem;
        object-fit: contain;
        filter: grayscale(100%) opacity(0.5);
        transition: filter 0.3s ease;
    }

    .ticker-logo:hover {
        filter: grayscale(0%) opacity(1);
    }

    @keyframes tickerScroll {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    @media (max-width: 768px) {
        .brand-ticker {
            padding: 0.75rem 0;
        }

        .ticker-item {
            padding: 0 1.5rem;
        }

        .ticker-text {
            font-size: 0.65rem;
        }

        .ticker-track {
            animation-duration: 18s;
        }

        .ticker-logo {
            max-height: 3.25rem;
            max-width: 10rem;
        }
    }
</style>
@endpush
