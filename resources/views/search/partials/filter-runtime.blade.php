<script>
(function () {
    const AJAX_URL = @json($ajaxUrl ?? route('search.ajax'));
    const CSRF_TOKEN = @json(csrf_token());
    const grid = document.getElementById('search-grid');
    const pagination = document.getElementById('search-pagination');
    const spinner = document.getElementById('search-spinner');
    const totalEl = document.getElementById('search-total');
    const filterForm = document.getElementById('filterSidebarForm');
    const chipsWrap = document.getElementById('search-active-filters');
    const chips = document.getElementById('search-active-filter-chips');
    const filterCount = document.getElementById('search-filter-count');
    const dynamicFacets = document.getElementById('search-dynamic-facets');
    let debounceTimer = null;
    let currentRequest = null;

    function canonicalFields() {
        return [
            ...document.querySelectorAll('[data-filter-context]'),
            ...filterForm.querySelectorAll('[data-filter]'),
            ...document.querySelectorAll('#sortForm select[data-filter]'),
        ];
    }

    function getParams() {
        const params = new URLSearchParams();
        canonicalFields().forEach(el => {
            if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
            const value = String(el.value ?? '').trim();
            if (!value) return;
            if (el.name.endsWith('[]')) params.append(el.name, value);
            else params.set(el.name, value);
        });
        return params;
    }

    function filterLabel(el) {
        if (el.dataset.selectedLabel) return el.dataset.selectedLabel;
        if (el.type === 'hidden' && el.id) {
            const textInput = document.querySelector(`[data-filter-combobox][aria-controls="${el.previousElementSibling?.nextElementSibling?.id || ''}"]`);
            if (textInput?.value) return textInput.value;
            const siblingInput = el.parentElement?.querySelector('[data-filter-combobox]');
            if (siblingInput?.value) return siblingInput.value;
        }
        if (el.name === 'sizes[]') return el.closest('label')?.querySelector('span')?.textContent?.trim() || `Tamanho ${el.value}`;
        if (el.name === 'colors[]') return 'Cor';
        if (el.name === 'coupon') return 'Com cupom vigente';
        if (el.name === 'min_price') return `A partir de ${filterForm.dataset.currencySign} ${el.value}`;
        if (el.name === 'max_price') return `Até ${filterForm.dataset.currencySign} ${el.value}`;
        return el.dataset.filterLabel || el.value;
    }

    function renderSelectedFilters() {
        const selected = [...filterForm.querySelectorAll('[data-filter]')].filter(el => {
            if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return false;
            return String(el.value ?? '').trim() !== '';
        });
        chips.innerHTML = '';
        selected.forEach(el => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.removeFilter = el.name;
            if (el.name.endsWith('[]')) button.dataset.filterValue = el.value;
            const label = document.createElement('span');
            label.textContent = filterLabel(el);
            button.appendChild(label);
            if (el.name === 'colors[]') {
                const swatch = document.createElement('span');
                swatch.className = 'search-chip-swatch';
                swatch.style.setProperty('--chip-color', el.value);
                button.appendChild(swatch);
            }
            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-xmark';
            button.appendChild(icon);
            chips.appendChild(button);
        });
        chipsWrap.classList.toggle('d-none', selected.length === 0);
        filterCount.textContent = selected.length;
        filterCount.classList.toggle('d-none', selected.length === 0);
    }

    function setLoading(loading) {
        spinner.classList.toggle('d-none', !loading);
        grid.classList.toggle('is-loading', loading);
        grid.setAttribute('aria-busy', loading ? 'true' : 'false');
    }

    function clearDependentFacets() {
        filterForm.querySelectorAll('[name="sizes[]"], [name="colors[]"], [name="coupon"]').forEach(el => { el.checked = false; });
        filterForm.querySelectorAll('[name="min_price"], [name="max_price"]').forEach(el => { el.value = ''; });
    }

    function bindFilterFields(scope = document) {
        scope.querySelectorAll('[data-filter]').forEach(el => {
            if (el.matches('[data-filter-context]') || el.dataset.filterRuntimeBound) return;
            el.dataset.filterRuntimeBound = '1';
            const eventName = el.tagName === 'SELECT' || el.type === 'checkbox' ? 'change' : 'input';
            el.addEventListener(eventName, () => {
                if (['brand', 'category', 'subcategory', 'categoriasfilhas'].includes(el.name)) clearDependentFacets();
                triggerSearch(eventName === 'change' ? 0 : 380);
            });
        });
    }

    function initPriceRange() {
        const rangeMin = document.getElementById('price-range-min');
        const rangeMax = document.getElementById('price-range-max');
        const inputMin = document.getElementById('min-price');
        const inputMax = document.getElementById('max-price');
        const rangeWrap = rangeMin?.closest('.search-price-range');
        if (!rangeMin || !rangeMax || !inputMin || !inputMax || rangeMin.dataset.rangeRuntimeBound) return;
        rangeMin.dataset.rangeRuntimeBound = rangeMax.dataset.rangeRuntimeBound = '1';
        const absoluteMin = Number(rangeMin.min);
        const absoluteMax = Number(rangeMin.max);
        const minimumGap = Number(rangeMin.step) || 1;
        const paint = () => {
            const span = Math.max(1, absoluteMax - absoluteMin);
            rangeWrap.style.setProperty('--min-pos', `${((Number(rangeMin.value) - absoluteMin) / span) * 100}%`);
            rangeWrap.style.setProperty('--max-pos', `${((Number(rangeMax.value) - absoluteMin) / span) * 100}%`);
        };
        const syncRanges = event => {
            if (Number(rangeMax.value) - Number(rangeMin.value) < minimumGap) {
                if (event.target === rangeMin) rangeMin.value = Number(rangeMax.value) - minimumGap;
                else rangeMax.value = Number(rangeMin.value) + minimumGap;
            }
            inputMin.value = Number(rangeMin.value) <= absoluteMin ? '' : rangeMin.value;
            inputMax.value = Number(rangeMax.value) >= absoluteMax ? '' : rangeMax.value;
            paint();
            (event.target === rangeMin ? inputMin : inputMax).dispatchEvent(new Event('input', { bubbles: true }));
        };
        const syncNumbers = () => {
            rangeMin.value = inputMin.value === '' ? absoluteMin : Math.max(absoluteMin, Number(inputMin.value));
            rangeMax.value = inputMax.value === '' ? absoluteMax : Math.min(absoluteMax, Number(inputMax.value));
            if (Number(rangeMin.value) > Number(rangeMax.value)) rangeMin.value = rangeMax.value;
            paint();
        };
        rangeMin.addEventListener('input', syncRanges);
        rangeMax.addEventListener('input', syncRanges);
        inputMin.addEventListener('input', syncNumbers);
        inputMax.addEventListener('input', syncNumbers);
        paint();
    }

    function doSearch(params, options = {}) {
        currentRequest?.abort();
        setLoading(true);
        const controller = new AbortController();
        currentRequest = controller;

        fetch(`${AJAX_URL}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
            signal: controller.signal,
        })
        .then(response => {
            if (!response.ok) throw new Error(`Busca indisponível (${response.status})`);
            return response.json();
        })
        .then(data => {
            grid.innerHTML = data.html;
            pagination.innerHTML = data.pagination;
            totalEl.textContent = new Intl.NumberFormat('pt-BR').format(data.total ?? 0);
            if (dynamicFacets && typeof data.facets === 'string') {
                dynamicFacets.innerHTML = data.facets;
                bindFilterFields(dynamicFacets);
                initPriceRange();
                renderSelectedFilters();
            }
            const query = params.toString();
            history.replaceState(null, '', query ? `${location.pathname}?${query}` : location.pathname);
            bindPaginationLinks();
            if (options.closeDrawer && window.bootstrap) {
                const drawer = document.getElementById('modalFiltros');
                bootstrap.Offcanvas.getInstance(drawer)?.hide();
            }
        })
        .catch(error => {
            if (error.name !== 'AbortError') console.error(error);
        })
        .finally(() => {
            if (currentRequest === controller) setLoading(false);
        });
    }

    function triggerSearch(delay = 350) {
        clearTimeout(debounceTimer);
        renderSelectedFilters();
        debounceTimer = setTimeout(() => doSearch(getParams()), delay);
    }

    function bindPaginationLinks() {
        pagination.querySelectorAll('a[href]').forEach(link => {
            link.addEventListener('click', event => {
                event.preventDefault();
                const page = new URL(link.href).searchParams.get('page');
                const params = getParams();
                if (page) params.set('page', page);
                doSearch(params);
                document.getElementById('search-status').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    bindFilterFields();
    initPriceRange();

    filterForm.addEventListener('submit', event => {
        event.preventDefault();
        clearTimeout(debounceTimer);
        doSearch(getParams(), { closeDrawer: true });
    });

    chipsWrap.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-filter]');
        if (!button) return;
        const selector = `[name="${CSS.escape(button.dataset.removeFilter)}"]`;
        const candidates = [...filterForm.querySelectorAll(selector)];
        const target = button.dataset.filterValue
            ? candidates.find(el => el.value === button.dataset.filterValue)
            : candidates[0];
        if (!target) return;
        if (target.type === 'checkbox' || target.type === 'radio') target.checked = false;
        else target.value = '';
        if (target.type === 'hidden') {
            target.dataset.selectedLabel = '';
            target.closest('.search-filter-group')?.querySelector('[data-filter-combobox]')?.setAttribute('value', '');
            const visible = target.closest('.search-filter-group')?.querySelector('[data-filter-combobox]');
            if (visible) visible.value = '';
        }
        if (target.name === 'min_price') {
            const range = document.getElementById('price-range-min');
            range.value = range.min;
            range.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (target.name === 'max_price') {
            const range = document.getElementById('price-range-max');
            range.value = range.max;
            range.dispatchEvent(new Event('input', { bubbles: true }));
        }
        triggerSearch(0);
    });

    document.querySelectorAll('[data-filter-suggestion]').forEach(button => {
        button.addEventListener('click', () => {
            const name = button.dataset.name;
            const value = button.dataset.value;
            const target = name === 'sort_by'
                ? document.querySelector(`#sortForm [name="${name}"]`)
                : filterForm.querySelector(`[name="${name}"]`);
            if (!target) return;
            target.value = value;
            if (['brand', 'category', 'subcategory', 'categoriasfilhas'].includes(name)) clearDependentFacets();
            if (target.type === 'hidden') {
                target.dataset.selectedLabel = button.textContent.trim();
                const visible = target.closest('.search-filter-group')?.querySelector('[data-filter-combobox]');
                if (visible) visible.value = button.textContent.trim();
            }
            triggerSearch(0);
        });
    });

    window.addEventListener('popstate', () => location.reload());
    bindPaginationLinks();
    renderSelectedFilters();
})();
</script>
