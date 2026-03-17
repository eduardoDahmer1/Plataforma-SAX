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

