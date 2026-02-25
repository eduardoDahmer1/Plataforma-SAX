{{-- SAX Bridal — Brand Ticker --}}
<div class="brand-ticker">
    <div class="ticker-track">
        @foreach($brands as $brand)
            @if(!empty($brand['logo_imagen']))
                <span class="ticker-item">
                    <img src="{{ asset('storage/' . $brand['logo_imagen']) }}" alt="{{ $brand['nombre'] }}" class="ticker-logo">
                </span>
            @endif
        @endforeach
        {{-- Duplicate for seamless infinite loop --}}
        @foreach($brands as $brand)
            @if(!empty($brand['logo_imagen']))
                <span class="ticker-item">
                    <img src="{{ asset('storage/' . $brand['logo_imagen']) }}" alt="{{ $brand['nombre'] }}" class="ticker-logo">
                </span>
            @endif
        @endforeach
    </div>
</div>

@push('styles')
<style>
    .brand-ticker {
        background: #ffffff;
        padding: 30px 0;
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
        padding: 0 40px;
    }

    .ticker-text {
        font-family: var(--font-display);
        font-size: 0.72rem;
        letter-spacing: 4px;
        color: var(--bridal-gold);
        font-weight: 400;
    }

    .ticker-logo {
        height: auto;
        width: auto;
        max-height: 36px;
        max-width: 140px;
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
            padding: 24px 0;
        }

        .ticker-item {
            padding: 0 24px;
        }

        .ticker-text {
            font-size: 0.65rem;
        }

        .ticker-logo {
            max-height: 28px;
            max-width: 100px;
        }
    }
</style>
@endpush
