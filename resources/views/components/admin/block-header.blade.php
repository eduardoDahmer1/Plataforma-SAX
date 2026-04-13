@props([
    'icon',
    'title',
    'number'      => null,
    'subtitle'    => null,
    'theme'       => 'gold',
    'actionLabel' => null,
    'actionId'    => null,
    'actionIcon'  => 'fas fa-plus',
])

<div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
    <div class="icon-circle-{{ $theme }}"><i class="{{ $icon }}"></i></div>
    <div class="flex-grow-1">
        <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">
            @if($number){{ $number }} — @endif{{ $title }}
        </p>
        @if($subtitle)
            <p class="x-small text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if($actionLabel)
        <button type="button" {{ $actionId ? "id=$actionId" : '' }}
                class="btn btn-sm btn-outline-dark rounded-pill x-small fw-bold px-3">
            <i class="{{ $actionIcon }} me-1"></i> {{ $actionLabel }}
        </button>
    @endif
</div>
