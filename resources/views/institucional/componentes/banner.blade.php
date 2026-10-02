@php $bannerImage = $sceneryUrls->get(3) ?: ($sceneryUrls->first() ?: asset('images/sax-og-image.jpg')); @endphp
<section class="inst-impact" aria-label="{{ $copy('inst_banner_title', 'O luxo ganha novos significados') }}">
    <img src="{{ $bannerImage }}" alt="" loading="lazy" data-random-scenery data-scenery-pool="{{ $sceneryUrls->toJson() }}">
    <span class="inst-impact__shade"></span>
    <div class="container inst-impact__content" data-aos="zoom-out">
        <i class="fa-solid fa-quote-left"></i>
        <h2>{{ $copy('inst_banner_title', 'O luxo ganha novos significados quando cada detalhe conta uma história') }}</h2>
        <span></span>
    </div>
</section>
