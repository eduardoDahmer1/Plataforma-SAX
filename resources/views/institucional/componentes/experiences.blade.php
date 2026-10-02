@php
    $fallbackExperienceImage = $sceneryUrls->first()
        ?: ($institucional->section_one_image
            ? asset('storage/'.$institucional->section_one_image)
            : asset('images/sax-og-image.jpg'));

    $experienceCards = [
        [
            'key' => 'department_store',
            'title' => 'SAX Department Store',
            'tag' => 'Moda & lifestyle',
            'text' => 'Marcas internacionais, beleza, casa e presentes em uma curadoria completa.',
            'route' => route('home'),
            'icon' => 'fa-bag-shopping',
            'class' => 'inst-experience-card--wide',
        ],
        [
            'key' => 'palace',
            'title' => 'SAX Palace',
            'tag' => 'Gastronomia & eventos',
            'text' => 'Alta gastronomia, adega, celebrações e uma atmosfera inesquecível.',
            'route' => route('palace.index'),
            'icon' => 'fa-crown',
            'class' => '',
        ],
        [
            'key' => 'bridal',
            'title' => 'SAX Bridal',
            'tag' => 'Noivas & celebrações',
            'text' => 'Consultoria especializada para transformar o grande dia em algo único.',
            'route' => route('bridal.index'),
            'icon' => 'fa-ring',
            'class' => '',
        ],
        [
            'key' => 'cafe_pjc',
            'title' => 'Café & Bistrô PJC',
            'tag' => 'Pedro Juan Caballero',
            'text' => 'Cafés de origem, receitas autorais e encontros em uma atmosfera singular.',
            'route' => route('cafe_bistro.index'),
            'icon' => 'fa-mug-hot',
            'class' => '',
        ],
        [
            'key' => 'cafe_asuncion',
            'title' => 'Café & Bistrô Asunción',
            'tag' => 'Shopping Dubai',
            'text' => 'Uma experiência gastronômica acolhedora no coração de Assunção.',
            'route' => route('cafe_bistro.show', 'asuncion'),
            'icon' => 'fa-mug-hot',
            'class' => '',
        ],
        [
            'key' => 'guide',
            'title' => 'Guia de setores',
            'tag' => 'Planeje sua visita',
            'text' => 'Descubra departamentos, serviços e encontre tudo o que procura na SAX.',
            'route' => route('contact.guide'),
            'icon' => 'fa-map-location-dot',
            'class' => 'inst-experience-card--guide',
        ],
    ];
@endphp
<section id="experiencias" class="inst-experiences">
    <div class="container">
        <header class="inst-section-head" data-aos="fade-up">
            <div>
                <p class="inst-kicker">{{ $copy('inst_experiences_eyebrow', 'Um universo, muitas experiências') }}</p>
                <h2>{{ $copy('inst_experiences_title', 'Descubra todas as formas de viver a SAX') }}</h2>
            </div>
            <p>{{ $copy('inst_experiences_description', 'Da curadoria de moda à gastronomia, dos grandes encontros aos momentos mais especiais: cada endereço revela uma nova faceta da nossa história.') }}</p>
        </header>

        <div class="inst-experiences__grid">
            @foreach($experienceCards as $card)
                @php $cardImage = data_get($experienceBanners ?? [], $card['key']) ?: $fallbackExperienceImage; @endphp
                <a class="inst-experience-card {{ $card['class'] }}" href="{{ $card['route'] }}"
                   data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 100 }}">
                    <img src="{{ $cardImage }}" alt="Banner {{ $card['title'] }}" loading="lazy">
                    <span class="inst-experience-card__shade"></span>
                    <span class="inst-experience-card__icon"><i class="fa-solid {{ $card['icon'] }}"></i></span>
                    <span class="inst-experience-card__content">
                        <small>{{ $card['tag'] }}</small>
                        <strong>{{ $card['title'] }}</strong>
                        <em>{{ $card['text'] }}</em>
                        <span>Explorar <i class="fa-solid fa-arrow-right"></i></span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
