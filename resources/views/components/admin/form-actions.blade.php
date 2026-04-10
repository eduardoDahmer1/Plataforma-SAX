@props([
    'cancelRoute',
    'submitLabel' => 'Salvar',
    'cancelLabel' => 'Cancelar',
    'submitIcon'  => 'fa-save',
])

<div class="mt-5 pt-4 border-top d-flex justify-content-end gap-2">
    <a href="{{ $cancelRoute }}" class="btn btn-action-sax fw-bold x-small text-uppercase px-4">
        <i class="fas fa-times me-1"></i> {{ $cancelLabel }}
    </a>
    <button type="submit" class="btn btn-dark fw-bold x-small text-uppercase px-5">
        <i class="fas {{ $submitIcon }} me-1"></i> {{ $submitLabel }}
    </button>
</div>
