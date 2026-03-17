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

