@php
    $selectedOpticalKeys = collect(old(
        "sections.{$key}.optical_item_keys",
        $section['optical_item_keys'] ?? []
    ))->map(fn ($value) => (string) $value);
    $typeLabels = [
        'category' => 'Categoria',
        'subcategory' => 'Subcategoria',
        'childcategory' => 'Categoria final',
    ];
@endphp

<section class="home-optical-picker" data-optical-picker>
    <div class="home-optical-picker__heading">
        <div>
            <span class="home-optical-picker__badge"><i class="fa-solid fa-glasses"></i> Somente layout Ótica</span>
            <strong>{{ $title }}</strong>
            <small>{{ $description }}</small>
        </div>
        <span class="home-optical-picker__counter" data-optical-count>0 selecionadas</span>
    </div>

    <label class="home-optical-picker__search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" class="form-control" placeholder="Buscar categoria pelo nome" data-optical-search>
    </label>

    <div class="home-optical-picker__items">
        @forelse($opticalItems as $opticalItem)
            @php($opticalKey = $opticalItem['type'].':'.$opticalItem['id'])
            <label class="home-optical-option" data-optical-option data-search-text="{{ mb_strtolower($opticalItem['label']) }}">
                <input type="checkbox"
                       name="sections[{{ $key }}][optical_item_keys][]"
                       value="{{ $opticalKey }}"
                       @checked($selectedOpticalKeys->contains($opticalKey))>
                <span class="home-optical-option__check"><i class="fa-solid fa-check"></i></span>
                <span class="home-optical-option__copy">
                    <strong>{{ $opticalItem['label'] }}</strong>
                    <small>{{ $typeLabels[$opticalItem['type']] ?? 'Categoria' }}</small>
                </span>
            </label>
        @empty
            <p class="home-optical-picker__empty">Nenhuma categoria da Ótica está disponível.</p>
        @endforelse
    </div>

    <div class="home-optical-picker__footer">
        <span>Sem seleção, o sistema mantém a escolha automática atual.</span>
        <button type="button" data-optical-clear>Limpar seleção</button>
    </div>
</section>
