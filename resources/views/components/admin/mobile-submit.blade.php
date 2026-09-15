@props([
    'formId'   => null,
    'label'    => 'GUARDAR CAMBIOS',
    'btnClass' => 'btn-dark-gold',
])

<div class="mobile-submit-bar d-md-none fixed-bottom" style="z-index:1030;">
    <button {{ $formId ? "form=$formId" : '' }} type="submit"
            class="btn {{ $btnClass }} w-100 fw-bold">
        <i class="fas fa-check-circle me-2"></i> {{ $label }}
    </button>
</div>
