@props([
    'title',
    'cancelRoute',
    'updatedAt'   => null,
    'divider'     => 'sax-divider-gold',
    'submitLabel' => 'GUARDAR CAMBIOS',
    'cancelLabel' => 'CANCELAR',
    'btnClass'    => 'btn-dark-gold',
])
<div class="sticky-header px-4 py-3 mb-5 bg-white border-bottom shadow-sm d-flex justify-content-between align-items-center">
    <div>
        <h2 class="sax-title text-uppercase letter-spacing-2 m-0">{{ $title }}</h2>
        <div class="{{ $divider }}"></div>
        @if($updatedAt)
            <span class="text-muted x-small">{{ $updatedAt }}</span>
        @endif
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="{{ $cancelRoute }}" class="btn-back-minimal d-none d-md-flex align-items-center">
            <i class="fas fa-times me-1"></i> {{ $cancelLabel }}
        </a>
        <button type="submit" class="btn {{ $btnClass }} rounded-pill px-4 fw-bold shadow-sm">
            <i class="fas fa-check-circle me-2"></i> {{ $submitLabel }}
        </button>
    </div>
</div>
