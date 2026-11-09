@props(['batch', 'heading' => false])

<div class="ai-batch-name" data-batch-rename data-name="{{ $batch->name }}" data-url="{{ route('admin.products.ai-batches.rename', $batch) }}">
    @if($heading)<h2 class="sax-title m-0">@endif
        <button type="button" class="ai-batch-name__trigger fw-bold" data-rename-start title="Hacé clic para cambiar el nombre" aria-label="Editar nombre del lote #{{ $batch->id }}">
            <span data-rename-label>{{ $batch->display_name }}</span>
            <i class="fa fa-pencil text-muted ms-1" aria-hidden="true"></i>
        </button>
    @if($heading)</h2>@endif
    <form data-rename-form hidden>
        @csrf
        <label class="visually-hidden" for="aiBatchName{{ $batch->id }}">Nombre del lote #{{ $batch->id }}</label>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <input type="text" id="aiBatchName{{ $batch->id }}" name="name" maxlength="120" class="form-control form-control-sm" style="width: min(100%, 320px)" placeholder="Ej.: Bolsas pendientes de fotos" aria-describedby="aiBatchNameStatus{{ $batch->id }}">
            <button type="submit" class="btn btn-sm btn-outline-dark">Guardar</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-rename-cancel>Cancelar</button>
        </div>
        <div class="form-text">Hasta 120 caracteres. Dejá vacío para volver al nombre original.</div>
    </form>
    <div id="aiBatchNameStatus{{ $batch->id }}" data-rename-status class="small" role="status" aria-live="polite"></div>
</div>

@once
    <style>
        .ai-batch-name__trigger { padding: 0; border: 0; background: transparent; color: inherit; font: inherit; text-align: left; overflow-wrap: anywhere; cursor: text; }
        .ai-batch-name__trigger:hover [data-rename-label] { text-decoration: underline; text-underline-offset: 3px; }
        .ai-batch-name__trigger:focus-visible { outline: 2px solid currentColor; outline-offset: 3px; }
        .ai-batch-name__trigger .fa-pencil { font-size: .7em; }
    </style>
    <script src="{{ asset('js/ai-batch-name.js') }}?v={{ filemtime(public_path('js/ai-batch-name.js')) }}" defer></script>
@endonce
