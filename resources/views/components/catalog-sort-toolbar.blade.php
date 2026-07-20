<form method="GET" action="{{ url()->current() }}" class="catalog-sort-toolbar search-toolbar mb-3" data-catalog-sort-form>
    @foreach(request()->except(['sort_by', 'per_page', 'page']) as $name => $value)
        @if(is_scalar($value))
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="toolbar-control d-flex align-items-center gap-2">
        <label class="toolbar-label d-none d-sm-block mb-0" for="catalog-sort-by">{{ __('messages.ordenar_por') }}</label>
        <select id="catalog-sort-by" name="sort_by" class="form-select toolbar-select">
            <option value="" @selected(!request('sort_by'))>{{ __('messages.ordenar_padrao') }}</option>
            <option value="latest" @selected(request('sort_by') === 'latest')>{{ __('messages.ordenar_ultimo') }}</option>
            <option value="price_low" @selected(request('sort_by') === 'price_low')>{{ __('messages.ordenar_menor_preco') }}</option>
            <option value="price_high" @selected(request('sort_by') === 'price_high')>{{ __('messages.ordenar_maior_preco') }}</option>
            <option value="name_az" @selected(request('sort_by') === 'name_az')>A–Z</option>
        </select>
    </div>

    <div class="toolbar-control d-flex align-items-center gap-2">
        <label class="toolbar-label d-none d-sm-block mb-0" for="catalog-per-page">{{ __('messages.mostrar') }}</label>
        <select id="catalog-per-page" name="per_page" class="form-select toolbar-select catalog-per-page-select">
            <option value="36" @selected((int) request('per_page', 36) === 36)>36</option>
            <option value="72" @selected((int) request('per_page') === 72)>72</option>
            <option value="100" @selected((int) request('per_page') === 100)>100</option>
        </select>
    </div>
</form>

<script>
    document.querySelectorAll('[data-catalog-sort-form] select').forEach(function (select) {
        select.addEventListener('change', function () {
            select.form.submit();
        });
    });
</script>
