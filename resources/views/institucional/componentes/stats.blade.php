@php
    $statsImage = $sceneryUrls->get(1) ?: ($sceneryUrls->first() ?: asset('images/sax-og-image.jpg'));
    $stats = [
        ['value' => $institucional->stat_brands_count ?? 200, 'suffix' => '+', 'label' => 'marcas internacionais'],
        ['value' => $institucional->stat_categories_count ?? 30, 'suffix' => '+', 'label' => 'categorias e setores'],
        ['value' => $institucional->stat_sqm_count ?? 17, 'suffix' => 'k m²', 'label' => 'de experiências'],
        ['value' => $institucional->stat_employees_count ?? 500, 'suffix' => '+', 'label' => 'especialistas SAX'],
    ];
@endphp
<section class="inst-stats">
    <img class="inst-stats__image" src="{{ $statsImage }}" alt="" loading="lazy" data-random-scenery data-scenery-pool="{{ $sceneryUrls->toJson() }}">
    <div class="inst-stats__shade"></div>
    <div class="container inst-stats__content">
        <header data-aos="fade-up"><p class="inst-kicker">{{ $copy('inst_stats_eyebrow', 'SAX em números') }}</p><h2>{{ $copy('inst_stats_title', 'Uma referência construída em escala humana') }}</h2></header>
        <div class="inst-stats__grid">
            @foreach($stats as $stat)
                <div class="inst-stat" data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                    <strong><span class="counter" data-target="{{ $stat['value'] }}">0</span><sup>{{ $stat['suffix'] }}</sup></strong>
                    <span>{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
