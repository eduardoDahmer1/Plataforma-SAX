{{-- SAX Bridal — Hero Section --}}
<section class="hero-bridal">
    {{-- Background image --}}
    <img src="{{ $backgroundImage }}" class="hero-bg" alt="SAX Bridal Hero">

    {{-- Gradient overlay for readability --}}
    <div class="hero-overlay"></div>

    {{-- Content --}}
    <div class="hero-body">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 col-xl-6">
                    <span class="hero-label">{{ $subtitle }}</span>
                    <h1 class="hero-heading">{!! $title !!}</h1>
                    <p class="hero-description">{{ $description }}</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ $primaryLink }}" class="btn-sax">{{ $primaryText }}</a>
                        <a href="{{ $secondaryLink }}" class="btn-sax-outline">{{ $secondaryText }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    .hero-bridal {
        position: relative;
        height: 100vh;
        max-height: 100vh;
        display: flex;
        align-items: flex-end;
        overflow: hidden;
    }

    .hero-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
    }

    .hero-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        background: linear-gradient(
            70deg,
            rgba(0, 0, 0, 0.55) 0%,
            rgba(0, 0, 0, 0.25) 45%,
            rgba(0, 0, 0, 0.05) 70%
        );
    }

    .hero-body {
        position: relative;
        z-index: 3;
        width: 100%;
        padding-bottom: 60px;
        padding-top: 100px;
    }

    .hero-label {
        display: inline-block;
        font-family: var(--font-display);
        font-size: 0.72rem;
        letter-spacing: 4px;
        color: var(--bridal-gold-light);
        text-transform: uppercase;
        margin-bottom: 14px;
    }

    .hero-heading {
        font-family: var(--font-serif);
        font-size: clamp(3rem, 5.5vw, 5rem);
        font-weight: 400;
        color: #fff;
        line-height: 1.15;
        margin-bottom: 18px;
        text-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
    }

    .hero-description {
        font-size: 0.92rem;
        color: rgba(255, 255, 255, 0.85);
        max-width: 480px;
        margin-bottom: 28px;
        line-height: 1.7;
    }

    /* Hero button overrides for light-on-dark */
    .hero-bridal .btn-sax {
        background: rgba(255, 255, 255, 0.95);
        color: var(--bridal-dark);
        font-size: 0.85rem;
        padding: 16px 44px;
    }

    .hero-bridal .btn-sax:hover {
        background: var(--bridal-gold);
        color: #fff;
    }

    .hero-bridal .btn-sax-outline {
        border-color: rgba(255, 255, 255, 0.6);
        color: #fff;
        font-size: 0.85rem;
        padding: 15px 42px;
    }

    .hero-bridal .btn-sax-outline:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: #fff;
        color: #fff;
    }

    @media (max-width: 991px) {
        .hero-bridal {
            height: 100vh;
            max-height: 100vh;
        }

        .hero-body {
            padding-top: 80px;
            padding-bottom: 40px;
        }
    }

    @media (max-width: 575px) {
        .hero-bridal {
            height: 100svh;
            max-height: 100svh;
        }

        .hero-heading {
            font-size: 2.4rem;
        }

        .hero-description {
            font-size: 0.9rem;
        }

        .hero-bridal .btn-sax,
        .hero-bridal .btn-sax-outline {
            padding: 13px 28px;
            font-size: 0.78rem;
        }
    }
</style>
@endpush
