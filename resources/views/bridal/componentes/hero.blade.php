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
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.40) 0%, transparent 100%);
        z-index: 2;
    }

    .hero-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        background: rgba(0, 0, 0, 0.25);
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
        font-size: 0.68rem;
        letter-spacing: 6px;
        color: #C6A76E;
        text-transform: uppercase;
        margin-bottom: 0.875rem;
        font-weight: 300;
        opacity: 0.95;
        text-shadow: 0px 2px 12px rgba(0, 0, 0, 0.18);
    }

    .hero-heading {
        font-family: var(--font-serif);
        font-size: clamp(3.5rem, 6.5vw, 6.5rem);
        font-weight: 400;
        color: #F4F1EA;
        line-height: 1.1;
        margin-bottom: 1.25rem;
        text-shadow: 0px 4px 18px rgba(0, 0, 0, 0.25);
    }

    .hero-description {
        font-size: 0.92rem;
        color: rgba(250, 247, 242, 0.80);
        max-width: 30rem;
        margin-bottom: 1.75rem;
        line-height: 1.7;
    }

    /* Hero buttons */
    .hero-bridal .btn-sax {
        background: #1C1C1C;
        color: #FAF7F2;
        padding: 1rem 2.75rem;
    }

    .hero-bridal .btn-sax:hover {
        background: #C6A76E;
        color: #fff;
    }

    .hero-bridal .btn-sax-outline {
        background: transparent;
        border-color: #C6A76E;
        color: #FAF7F2;
        padding: 1rem 2.75rem;
    }

    .hero-bridal .btn-sax-outline:hover {
        background: #C6A76E;
        border-color: #C6A76E;
        color: #fff;
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
