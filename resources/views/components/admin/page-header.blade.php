@props([
    'title',
    'description',
    'divider'     => 'sax-divider-dark',
    'actionUrl'   => null,
    'actionLabel' => null,
    'actionIcon'  => 'fa fa-plus',
])

<div class="dashboard-header d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
    <div>
        <h2 class="sax-title text-uppercase letter-spacing-2 m-0">{{ $title }}</h2>
        <div class="{{ $divider }}"></div>
        <div class="text-muted x-small mt-2 mb-0">{!! $description !!}</div>
    </div>
    @if(isset($actions))
        <div>{{ $actions }}</div>
    @elseif($actionUrl && $actionLabel)
        <div>
            <a href="{{ $actionUrl }}"
               class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                <i class="{{ $actionIcon }} me-2"></i> {{ $actionLabel }}
            </a>
        </div>
    @endif
</div>
