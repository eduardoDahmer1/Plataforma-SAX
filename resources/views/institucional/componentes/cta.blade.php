@php $ctaImage = $sceneryUrls->get(2) ?: ($sceneryUrls->first() ?: asset('images/sax-og-image.jpg')); @endphp
<section class="inst-cta">
    <img src="{{ $ctaImage }}" alt="" loading="lazy" data-random-scenery data-scenery-pool="{{ $sceneryUrls->toJson() }}">
    <span class="inst-cta__shade"></span>
    <div class="container inst-cta__content" data-aos="fade-up">
        <p class="inst-kicker">{{ $copy('inst_cta_eyebrow', 'Viva a experiência') }}</p>
        <h2>{{ $copy('inst_cta_title', 'Sua próxima descoberta começa aqui') }}</h2>
        <p>{{ $copy('inst_cta_description', 'Planeje sua visita e descubra pessoalmente tudo o que faz da SAX um destino singular no Paraguai.') }}</p>
        <div class="inst-cta__actions">
            <a href="{{ route('contact.form') }}" class="inst-button inst-button--gold">{{ $copy('inst_cta_button', 'Planejar minha visita') }} <i class="fa-solid fa-arrow-right"></i></a>
            <a href="{{ route('contact.guide') }}" class="inst-button inst-button--outline">Ver guia de setores <i class="fa-solid fa-map"></i></a>
        </div>
    </div>
</section>
