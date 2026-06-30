@extends('layout.layout')

@section('content')

<x-alert type="success" :message="session('success')" />

<div class="container-fluid px-3 px-lg-5 py-5">

    @if ($query)
        <div class="mb-5">
            <p class="x-small text-uppercase text-muted tracking-widest mb-1">{{ __('messages.resultados_para') }}</p>
            <h1 class="h4 fw-light text-uppercase" style="letter-spacing: 2px;">"{{ $query }}"</h1>
        </div>
    @endif

    <x-sidebar-filters
        :request="request()"
        :brands="$brands"
        :categories="$categories"
        :subcategories="$subcategories"
        :categoriasfilhas="$categoriasfilhas"
    />

    <div id="search-status" class="d-flex align-items-center gap-2 mb-3 x-small text-muted text-uppercase tracking-widest" style="min-height: 24px;">
        <span id="search-total"></span>
        <div id="search-spinner" class="spinner-border spinner-border-sm d-none" role="status" style="width: 12px; height: 12px; border-width: 1.5px;"></div>
    </div>

    <div id="search-grid" class="row g-2 g-md-3">
        @include('search.partials.grid', ['paginated' => $paginated])
    </div>

    <div id="search-pagination" class="d-flex justify-content-center mt-5 pagination-sax">
        @include('search.partials.pagination', ['paginated' => $paginated])
    </div>

</div>

<script>
(function () {
    const AJAX_URL   = '{{ route("search.ajax") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';

    const grid       = document.getElementById('search-grid');
    const pagination = document.getElementById('search-pagination');
    const spinner    = document.getElementById('search-spinner');
    const totalEl    = document.getElementById('search-total');

    let debounceTimer = null;
    let currentRequest = null;

    function getParams() {
        const params = new URLSearchParams();
        document.querySelectorAll('[data-filter]').forEach(el => {
            const val = el.value?.trim();
            if (val) params.set(el.name, val);
        });
        const page = new URLSearchParams(location.search).get('page');
        if (page) params.set('page', page);
        return params;
    }

    function doSearch(params) {
        if (currentRequest) currentRequest.abort();

        spinner.classList.remove('d-none');
        grid.style.opacity = '0.4';

        const url = AJAX_URL + '?' + params.toString();

        const controller = new AbortController();
        currentRequest   = controller;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
            signal: controller.signal,
        })
        .then(r => r.json())
        .then(data => {
            grid.innerHTML       = data.html;
            pagination.innerHTML = data.pagination;

            const count = data.total ?? 0;
            totalEl.textContent  = count > 0 ? count + ' {{ __("messages.produtos") }}' : '';

            grid.style.opacity = '1';
            spinner.classList.add('d-none');

            history.replaceState(null, '', '?' + params.toString());

            bindPaginationLinks();
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                grid.style.opacity = '1';
                spinner.classList.add('d-none');
            }
        });
    }

    function triggerSearch(delay = 400) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => doSearch(getParams()), delay);
    }

    function bindPaginationLinks() {
        pagination.querySelectorAll('a[href]').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const page = new URL(link.href).searchParams.get('page');
                const params = getParams();
                if (page) params.set('page', page);
                doSearch(params);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }

    document.querySelectorAll('[data-filter]').forEach(el => {
        const event = (el.tagName === 'SELECT') ? 'change' : 'input';
        el.addEventListener(event, () => {
            triggerSearch(el.tagName === 'SELECT' ? 0 : 450);
        });
    });

    document.getElementById('filterSidebarForm')?.addEventListener('submit', e => {
        e.preventDefault();
        doSearch(getParams());
    });

    document.getElementById('sortForm')?.addEventListener('change', () => {
        doSearch(getParams());
    });

    bindPaginationLinks();
})();
</script>

@endsection
