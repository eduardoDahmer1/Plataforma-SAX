<section class="inst-videos">
    <div class="container">
        <header class="inst-section-head" data-aos="fade-up">
            <div><p class="inst-kicker">{{ $copy('inst_videos_eyebrow', 'Paraguai ao vivo') }}</p><h2>{{ $copy('inst_videos_title', 'Conectados com Ciudad del Este') }}</h2></div>
            <p>{{ $copy('inst_videos_description', 'Acompanhe as principais vias de acesso e explore nossos espaços antes mesmo de chegar.') }}</p>
        </header>
        <div class="inst-videos__grid">
            @if(!empty($institucional->iframe_ponte_amizade))
                <article class="inst-video-card" data-aos="fade-up"><div class="inst-video-card__head"><span><i class="fa-solid fa-video"></i> Ao vivo</span><h3>Ponte da Amizade</h3></div><div class="inst-video-card__frame">{!! $institucional->iframe_ponte_amizade !!}</div></article>
            @endif
            @if(!empty($institucional->iframe_centro_cde))
                <article class="inst-video-card" data-aos="fade-up" data-aos-delay="100"><div class="inst-video-card__head"><span><i class="fa-solid fa-video"></i> Ao vivo</span><h3>Centro de Ciudad del Este</h3></div><div class="inst-video-card__frame">{!! $institucional->iframe_centro_cde !!}</div></article>
            @endif
            @if(!empty($institucional->iframe_tour_360))
                <article class="inst-video-card inst-video-card--wide" data-aos="fade-up"><div class="inst-video-card__head"><span><i class="fa-solid fa-street-view"></i> Experiência imersiva</span><h3>Tour virtual SAX</h3></div><div class="inst-video-card__frame">{!! $institucional->iframe_tour_360 !!}</div></article>
            @endif
        </div>
    </div>
</section>
