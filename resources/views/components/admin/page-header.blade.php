@props(['title', 'description', 'divider' => 'sax-divider-dark'])

<div class="dashboard-header d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
    <div>
        <h2 class="sax-title text-uppercase letter-spacing-2 m-0">{{ $title }}</h2>
        <div class="{{ $divider }}"></div>
        <div class="text-muted x-small mt-2 mb-0">{!! $description !!}</div>
    </div>
    @if(isset($actions))
        <div>{{ $actions }}</div>
    @endif
</div>
