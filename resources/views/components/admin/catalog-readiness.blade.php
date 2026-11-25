@props([
    'percentage' => 0,
    'size' => 'small',
    'id' => null,
    'title' => null,
])

@php
    $percentage = max(0, min(100, (int) $percentage));
    $state = $percentage === 100 ? 'complete' : ($percentage >= 50 ? 'warning' : 'danger');
    $title ??= $percentage === 100
        ? 'Catálogo completo'
        : $percentage.'% de los datos del catálogo están completos';
@endphp

<span @if($id) id="{{ $id }}" @endif
    {{ $attributes->class(['catalog-readiness', 'catalog-readiness--'.$size, 'catalog-readiness--'.$state]) }}
    style="--readiness: {{ $percentage }}"
    role="progressbar"
    aria-valuemin="0"
    aria-valuemax="100"
    aria-valuenow="{{ $percentage }}"
    aria-label="{{ $title }}"
    title="{{ $title }}">
    <span class="catalog-readiness__ring" aria-hidden="true"></span>
    <strong class="catalog-readiness__value">{{ $percentage }}%</strong>
</span>

@once
    @push('styles')
        <style>
            .catalog-readiness {
                --readiness-color: #dc3545;
                position: relative;
                display: inline-grid;
                width: 48px;
                height: 48px;
                flex: 0 0 48px;
                place-items: center;
                color: #263241;
                vertical-align: middle;
            }
            .catalog-readiness--large { width: 68px; height: 68px; flex-basis: 68px; }
            .catalog-readiness--warning { --readiness-color: #e5a100; }
            .catalog-readiness--complete { --readiness-color: #198754; }
            .catalog-readiness__ring {
                position: absolute;
                inset: 0;
                border-radius: 50%;
                background: conic-gradient(
                    var(--readiness-color) max(calc(var(--readiness) * 1%), 2%),
                    #e6ebf1 0
                );
                animation: catalog-readiness-spin 2.8s linear infinite;
            }
            .catalog-readiness__ring::after {
                position: absolute;
                inset: 5px;
                content: '';
                border-radius: 50%;
                background: #fff;
                box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .04);
            }
            .catalog-readiness--large .catalog-readiness__ring::after { inset: 7px; }
            .catalog-readiness--complete .catalog-readiness__ring { animation: none; }
            .catalog-readiness__value {
                position: relative;
                z-index: 1;
                font-size: .67rem;
                font-variant-numeric: tabular-nums;
                line-height: 1;
            }
            .catalog-readiness--large .catalog-readiness__value { font-size: .9rem; }
            @keyframes catalog-readiness-spin { to { transform: rotate(360deg); } }
            @media (prefers-reduced-motion: reduce) {
                .catalog-readiness__ring { animation: none; }
            }
        </style>
    @endpush
@endonce
