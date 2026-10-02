@php
    $pillars = [
        ['icon' => 'fa-gem', 'title' => $translation->inst_text_section_one_title ?? $institucional->text_section_one_title ?? 'Curadoria', 'body' => $translation->inst_text_section_one_body ?? $institucional->text_section_one_body ?? 'Uma seleção internacional pensada em cada detalhe.'],
        ['icon' => 'fa-hand-holding-heart', 'title' => $translation->inst_text_section_two_title ?? $institucional->text_section_two_title ?? 'Hospitalidade', 'body' => $translation->inst_text_section_two_body ?? $institucional->text_section_two_body ?? 'Atendimento que transforma cada visita em uma lembrança.'],
        ['icon' => 'fa-compass', 'title' => $translation->inst_text_section_three_title ?? $institucional->text_section_three_title ?? 'Descoberta', 'body' => $translation->inst_text_section_three_body ?? $institucional->text_section_three_body ?? 'Novas formas de viver moda, sabor, celebração e cultura.'],
    ];
@endphp
<section class="inst-pillars" aria-label="Pilares SAX">
    <div class="container">
        <div class="inst-pillars__grid">
            @foreach($pillars as $pillar)
                <article class="inst-pillar" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    <span class="inst-pillar__number">0{{ $loop->iteration }}</span>
                    <i class="fa-solid {{ $pillar['icon'] }}"></i>
                    <h3>{{ $pillar['title'] }}</h3>
                    <p>{{ $pillar['body'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
