@php
    $sizeGroups = $sizeGroups ?? [];
    $colors = $colors ?? [];
    $couponProductCount = $couponProductCount ?? 0;
    $priceBounds = $priceBounds ?? ['min' => 0, 'max' => 0, 'selected_min' => 0, 'selected_max' => 0];
    $currencyContext = $currencyContext ?? ['sign' => 'US$', 'step' => .01, 'decimals' => 2];
@endphp
@php
    $selectedSizes = collect((array) $request->input('sizes', []))->map(fn ($size) => strtoupper(str_replace(' ', '', (string) $size)))->filter()->values();
    $selectedColors = collect((array) $request->input('colors', []))->map(fn ($color) => '#'.strtoupper(ltrim((string) $color, '#')))->filter()->values();
    $numberFormat = fn ($value) => number_format((float) $value, $currencyContext['decimals'], ',', '.');
@endphp

<section class="search-filter-section">
    <div class="search-filter-section__heading">
        <h6>Faixa de preço</h6>
        <span>{{ $currencyContext['sign'] }} {{ $numberFormat($priceBounds['min']) }} — {{ $currencyContext['sign'] }} {{ $numberFormat($priceBounds['max']) }}</span>
    </div>
    <div class="search-price-range" style="--min-pos: 0%; --max-pos: 100%;">
        <input type="range" id="price-range-min" min="{{ $priceBounds['min'] }}" max="{{ $priceBounds['max'] }}" step="{{ $currencyContext['step'] }}" value="{{ $priceBounds['selected_min'] }}" aria-label="Preço mínimo">
        <input type="range" id="price-range-max" min="{{ $priceBounds['min'] }}" max="{{ $priceBounds['max'] }}" step="{{ $currencyContext['step'] }}" value="{{ $priceBounds['selected_max'] }}" aria-label="Preço máximo">
    </div>
    <div class="search-price-grid">
        <div><span>De</span><input type="number" id="min-price" name="min_price" data-filter data-filter-label="Preço mínimo" min="{{ $priceBounds['min'] }}" max="{{ $priceBounds['max'] }}" step="{{ $currencyContext['step'] }}" class="form-control sax-filter-input" placeholder="{{ $numberFormat($priceBounds['min']) }}" value="{{ $request->min_price }}"></div>
        <div><span>Até</span><input type="number" id="max-price" name="max_price" data-filter data-filter-label="Preço máximo" min="{{ $priceBounds['min'] }}" max="{{ $priceBounds['max'] }}" step="{{ $currencyContext['step'] }}" class="form-control sax-filter-input" placeholder="{{ $numberFormat($priceBounds['max']) }}" value="{{ $request->max_price }}"></div>
    </div>
</section>

@if(collect($sizeGroups)->isNotEmpty())
    <section class="search-filter-section">
        <div class="search-filter-section__heading"><h6>Tamanhos e medidas</h6><span>Somente opções deste catálogo</span></div>
        @foreach($sizeGroups as $sizeGroup)
            <div class="search-size-group">
                <strong>{{ $sizeGroup->label }}</strong>
                <div class="search-option-grid search-option-grid--sizes">
                    @foreach($sizeGroup->options as $size)
                        <label>
                            <input type="checkbox" name="sizes[]" data-filter value="{{ $size->key }}" @checked($selectedSizes->contains((string) $size->key))>
                            <span>{{ $size->label }}</span><small>{{ $size->total }}</small>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
@endif

@if(collect($colors)->isNotEmpty())
    <section class="search-filter-section">
        <div class="search-filter-section__heading"><h6>Cor</h6><span>Cores disponíveis</span></div>
        <div class="search-color-grid">
            @foreach($colors as $color)
                @php($hex = '#'.strtoupper(ltrim((string) $color->color, '#')))
                <label title="{{ $hex }}">
                    <input type="checkbox" name="colors[]" data-filter value="{{ $hex }}" @checked($selectedColors->contains($hex))>
                    <span style="--swatch: {{ $hex }}"></span><small>{{ $color->total }}</small>
                </label>
            @endforeach
        </div>
    </section>
@endif

@if($couponProductCount)
    <section class="search-filter-section">
        <h6>Benefícios</h6>
        <div class="search-toggle-list">
            <label>
                <span><i class="fa-solid fa-ticket"></i><b>Com cupom vigente</b><small>{{ $couponProductCount }} produtos elegíveis</small></span>
                <input type="checkbox" name="coupon" data-filter value="1" @checked($request->boolean('coupon'))>
            </label>
        </div>
    </section>
@endif
