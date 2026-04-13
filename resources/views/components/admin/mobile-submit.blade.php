@props([
    'formId'   => null,
    'label'    => 'GUARDAR CAMBIOS',
    'btnClass' => 'btn-dark-gold',
])

<div class="d-md-none fixed-bottom p-3 bg-white border-top shadow-lg" style="z-index:1030;">
    <button {{ $formId ? "form=$formId" : '' }} type="submit"
            class="btn {{ $btnClass }} w-100 py-3 rounded-pill fw-bold">
        <i class="fas fa-check-circle me-2"></i> {{ $label }}
    </button>
</div>
