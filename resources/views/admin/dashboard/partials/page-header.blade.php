<header class="dashboard-section-header mb-4">
    <a href="{{ route('admin.index') }}" class="dashboard-section-header__back"><i class="fa-solid fa-arrow-left"></i> Visão geral</a>
    <div class="dashboard-section-header__main">
        <span class="dashboard-section-header__icon"><i class="fa-solid {{ $icon }}"></i></span>
        <div>
            <div class="card-kicker">{{ $eyebrow }}</div>
            <h1>{{ $title }}</h1>
            <p>{{ $description }}</p>
        </div>
    </div>
    @isset($actionRoute)
        <a href="{{ $actionRoute }}" class="btn btn-dark dashboard-section-header__action">{{ $actionLabel }} <i class="fa-solid fa-arrow-right ms-1"></i></a>
    @endisset
</header>
