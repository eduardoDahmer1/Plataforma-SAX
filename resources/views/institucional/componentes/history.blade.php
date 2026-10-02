@php
    $defaults = [
        ['year' => '2008', 'label' => 'Origem', 'title' => 'O nascimento de uma visão', 'text' => 'A SAX nasce no Paraguai para reunir curadoria internacional, arquitetura e hospitalidade.'],
        ['year' => '2012', 'label' => 'Ciudad del Este', 'title' => 'Um destino ganha forma', 'text' => 'A experiência se consolida como referência regional em moda, beleza, casa e lifestyle.'],
        ['year' => '2023', 'label' => 'Novos capítulos', 'title' => 'Experiências para momentos únicos', 'text' => 'Bridal, Palace e Café & Bistrô ampliam as possibilidades do universo SAX.'],
        ['year' => 'Hoje', 'label' => 'Legado em movimento', 'title' => 'Sempre uma nova descoberta', 'text' => 'A mesma assinatura continua evoluindo com novas marcas, sabores e encontros.'],
    ];
    $milestones = collect($institucional->history_milestones ?: $defaults)->map(function ($item) use ($dbLocale) {
        if (isset($item[$dbLocale])) {
            $item['title'] = data_get($item, "$dbLocale.title") ?: data_get($item, 'pt-br.title');
            $item['text'] = data_get($item, "$dbLocale.text") ?: data_get($item, 'pt-br.text');
        }
        return $item;
    });
@endphp
<section class="inst-history">
    <div class="container">
        <header class="inst-section-head" data-aos="fade-up">
            <div><p class="inst-kicker">{{ $copy('inst_history_eyebrow', 'Nosso legado') }}</p><h2>{{ $copy('inst_history_title', 'Uma trajetória movida por visão') }}</h2></div>
            <p>{{ $copy('inst_history_intro', 'Do primeiro projeto ao ecossistema de experiências de hoje, nossa história é feita de encontros, descobertas e um olhar permanente para o futuro.') }}</p>
        </header>
        <div class="inst-history__track">
            @foreach($milestones as $milestone)
                <article class="inst-history__item" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 4) * 80 }}">
                    <span class="inst-history__year">{{ $milestone['year'] ?? '' }}</span>
                    <span class="inst-history__dot"></span>
                    <small>{{ $milestone['label'] ?? 'SAX' }}</small>
                    <h3>{{ $milestone['title'] ?? '' }}</h3>
                    <p>{{ $milestone['text'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
