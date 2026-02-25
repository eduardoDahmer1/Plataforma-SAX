{{-- SAX Bridal — Palace Banner --}}
<section class="palace-banner" id="palace">
    <img src="{{ $backgroundImage }}" alt="Palace Experience" class="palace-banner-bg" loading="lazy">
    <div class="palace-banner-overlay"></div>

    <div class="palace-banner-body text-center">
        <span class="title-gold" style="color: var(--bridal-gold-light);">{{ $subtitle }}</span>
        <h2 class="palace-heading">{!! $title !!}</h2>
        <p class="palace-desc">{{ $description }}</p>
        <a href="{{ $link }}" class="btn-palace-cta">{{ $buttonText }}</a>
    </div>
</section>

@push('styles')
<style>
    .palace-banner {
        position: relative;
        height: 60vh;
        max-height: 540px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .palace-banner-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
    }

    .palace-banner-overlay {
        position: absolute;
        inset: 0;
        z-index: 2;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.45) 0%, rgba(44, 44, 44, 0.6) 100%);
    }

    .palace-banner-body {
        position: relative;
        z-index: 3;
        color: var(--bridal-white);
        max-width: 600px;
        padding: 0 24px;
    }

    .palace-heading {
        font-family: var(--font-display);
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-bottom: 16px;
    }

    .palace-desc {
        font-family: var(--font-serif);
        font-size: 1.05rem;
        font-style: italic;
        margin-bottom: 32px;
        opacity: 0.92;
        line-height: 1.7;
    }

    .btn-palace-cta {
        background: var(--bridal-gold);
        color: var(--bridal-white);
        padding: 14px 36px;
        border-radius: 2px;
        text-decoration: none;
        font-size: 0.7rem;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        display: inline-block;
        font-weight: 500;
        transition: var(--transition);
    }

    .btn-palace-cta:hover {
        background: var(--bridal-white);
        color: var(--bridal-gold);
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(255, 255, 255, 0.2);
    }

    @media (max-width: 767px) {
        .palace-banner {
            height: 50vh;
            max-height: 420px;
        }

        .palace-heading {
            font-size: 1.8rem;
        }

        .palace-desc {
            font-size: 0.92rem;
        }
    }
</style>
@endpush
