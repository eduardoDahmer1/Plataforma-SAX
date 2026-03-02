{{-- SAX Bridal — Hero Section --}}
<section class="hero-bridal">
    {{-- Background image --}}
    <img src="{{ $backgroundImage }}" class="hero-bg" alt="SAX Bridal Hero"
         fetchpriority="high" decoding="async">

    {{-- Top gradient: darkens only the navbar strip for legibility --}}
    <div class="hero-top-gradient"></div>

    {{-- Dark overlay for text contrast --}}
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

    .hero-top-gradient {
        position: absolute;
        inset: 0;
        height: 40vh;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.45) 0%, transparent 100%);
        z-index: 2;
    }

    .hero-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        background: linear-gradient(
            to bottom,
            rgba(0, 0, 0, 0.15) 0%,
            rgba(0, 0, 0, 0.55) 100%
        );
    }

    .hero-body {
        position: relative;
        z-index: 3;
        width: 100%;
        padding-bottom: 3.75rem;
        padding-top: 6.25rem;
    }

    .hero-label {
        display: inline-block;
        font-family: var(--font-display);
        font-size: 0.75rem;
        letter-spacing: 6px;
        color: #e2c991;
        text-transform: uppercase;
        margin-bottom: 0.875rem;
        font-weight: 500;
        text-shadow: 0px 2px 12px rgba(0, 0, 0, 0.4);
    }

    .hero-heading {
        font-family: var(--font-serif);
        font-size: clamp(3.5rem, 6.5vw, 6.5rem);
        font-weight: 400;
        color: #FFFFFF;
        line-height: 1.1;
        margin-bottom: 1.25rem;
        text-shadow: 0px 4px 24px rgba(0, 0, 0, 0.45);
    }

    .hero-description {
        font-size: 0.92rem;
        color: rgba(255, 255, 255, 0.92);
        max-width: 30rem;
        margin-bottom: 1.75rem;
        line-height: 1.7;
        text-shadow: 0px 2px 8px rgba(0, 0, 0, 0.3);
    }

    /* Hero buttons */
    .hero-bridal .btn-sax {
        background: #C6A76E;
        color: bridal-white;
        padding: 1rem 2.75rem;
        font-weight: 600;
        box-shadow: 0 4px 16px rgba(198, 167, 110, 0.35);
    }

    .hero-bridal .btn-sax:hover {
        background: #d4b87e;
        color: bridal-white;
        box-shadow: 0 6px 24px rgba(198, 167, 110, 0.45);
    }

    .hero-bridal .btn-sax-outline {
        border: 2px solid rgba(255, 255, 255, 0.85);
        background: rgba(255, 255, 255, 0.08);
        color: #FFFFFF;
        padding: 1rem 2.75rem;
        backdrop-filter: blur(4px);
    }

    .hero-bridal .btn-sax-outline:hover {
        background: rgba(255, 255, 255, 0.18);
        border-color: #FFFFFF;
        color: #FFFFFF;
    }

    @media (max-width: 991px) {
        .hero-bridal {
            height: 100vh;
            max-height: 100vh;
        }

        .hero-body {
            padding-top: 5rem;
            padding-bottom: 2.5rem;
        }
    }

    @media (max-width: 575px) {
        .hero-bridal {
            height: 100svh;
            max-height: 100svh;
        }

        .hero-heading {
            font-size: 2.8rem;
        }

        .hero-description {
            font-size: 0.9rem;
        }

        .hero-bridal .btn-sax,
        .hero-bridal .btn-sax-outline {
            padding: 0.8125rem 1.75rem;
        }
    }
</style>
@endpush
