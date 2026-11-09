@extends('layout.admin')

@section('content')
<style>
    #waStart {
        min-height: 38px;
        padding: 6px 12px;
        border: 1px solid #00b832 !important;
        border-radius: 3px !important;
        background: #00b832 !important;
        color: #fff !important;
        font-size: .7rem;
        font-weight: 700;
        line-height: 1.5;
        box-shadow: none !important;
        transform: none !important;
    }
    #waStart:hover {
        background: #009e2b !important;
        border-color: #009e2b !important;
    }
    #waStart:focus-visible {
        outline: 2px solid #212529;
        outline-offset: 2px;
    }
    #waBuildings .form-check-input[type="radio"] {
        border-radius: .2em !important;
    }
    #waBuildings .form-check-input[type="radio"]:checked {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e");
    }
</style>
<x-admin.card>
    <x-admin.page-header
        :title="$batch->display_name"
        description="Lote #{{ $batch->id }} · {{ $batch->original_filename ?: 'Códigos pegados manualmente' }} · creado por {{ $batch->creator?->name ?: 'Administrador' }}">
        <x-slot:heading><x-admin.ai-batch-name :batch="$batch" :heading="true" /></x-slot:heading>
        <x-slot:actions>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <button type="button" class="btn" id="waStart"><i class="fa-brands fa-whatsapp me-2" aria-hidden="true"></i>Preparar consulta a sector por WhatsApp</button>
            <a href="{{ route('admin.products.ai-batches.index') }}" class="btn btn-outline-dark btn-sax-lg px-4">
                <i class="fa fa-arrow-left me-2"></i> Nuevo lote
            </a>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />
    <x-admin.ai-availability />
    @error('ai')<div class="alert alert-danger">{{ $message }}</div>@enderror
    @error('batch')<div class="alert alert-danger">{{ $message }}</div>@enderror
    @error('skus')<div class="alert alert-danger">{{ $message }}</div>@enderror

    <div id="aiBatchProgress" data-status-url="{{ route('admin.products.ai-batches.status', $batch) }}" data-batch-status="{{ $batch->status }}">
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg"><div class="card border-0 shadow-sm p-3"><small class="text-muted">Códigos</small><strong class="fs-4">{{ $batch->input_count }}</strong></div></div>
            <div class="col-6 col-lg"><div class="card border-0 shadow-sm p-3"><small class="text-muted">A procesar</small><strong class="fs-4">{{ $batch->eligible_count }}</strong></div></div>
            <div class="col-6 col-lg"><div class="card border-0 shadow-sm p-3"><small class="text-muted">Completados</small><strong id="countCompleted" class="fs-4 text-success">{{ $counts['completed'] ?? 0 }}</strong></div></div>
            <div class="col-6 col-lg"><div class="card border-0 shadow-sm p-3"><small class="text-muted">En proceso</small><strong id="countActive" class="fs-4 text-primary">{{ ($counts['queued'] ?? 0) + ($counts['processing'] ?? 0) }}</strong></div></div>
            <div class="col-6 col-lg"><div class="card border-0 shadow-sm p-3"><small class="text-muted">Revisión</small><strong id="countReview" class="fs-4 text-danger">{{ ($counts['not_found'] ?? 0) + ($counts['failed'] ?? 0) }}</strong></div></div>
        </div>

        @if($batch->status === 'draft')
            <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <strong>Vista previa lista.</strong> Confirmar iniciará {{ $batch->eligible_count }} llamadas en segundo plano.
                    <div class="small mt-1">
                        {{ $batch->duplicate_count }} duplicados · {{ $groupedCount }} variantes agrupadas ·
                        {{ $counts['missing'] ?? 0 }} inexistentes · {{ $counts['skipped'] ?? 0 }} omitidos
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-dark fw-bold" data-bs-toggle="modal" data-bs-target="#aiBatchCatalogModal">
                        <i class="fa fa-plus me-2"></i> Agregar productos
                    </button>
                    @if($batch->eligible_count > 0)
                        <form method="POST" action="{{ route('admin.products.ai-batches.dispatch', $batch) }}">
                            @csrf
                            <button class="btn btn-primary fw-bold" @disabled(app(\App\Services\ProductAiSettingsService::class)->unavailableReason()) onclick="this.disabled=true; this.form.submit();">
                                <i class="fa fa-play me-2"></i> Generar información
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @else
            <div class="progress mb-4" style="height: 22px;">
                <div id="batchProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
            </div>
        @endif

        <div id="waConsultation" class="card border-0 shadow-sm">
                <div id="waSelectionActions" class="p-3 border-bottom d-flex flex-wrap align-items-center gap-2" hidden style="display:none !important">
                    <label class="me-2"><input type="checkbox" id="waSelectAll"> Seleccionar todos</label>
                    <span id="waCount" aria-live="polite">0 seleccionados</span>
                    <button type="button" class="btn btn-success" id="waContinue" disabled>Continuar con la consulta</button>
                    <button type="button" class="btn btn-outline-secondary" id="waCancel">Cancelar</button>
                </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th class="wa-select-cell" hidden scope="col"><span class="visually-hidden">Seleccionar</span></th><th>Producto</th><th>Códigos recibidos</th><th>Estado</th><th>Detalle y fuentes</th><th></th></tr></thead>
                    <tbody>
                    @foreach($batch->items as $item)
                        @php
                            $statusPresentation = [
                                'ready' => ['Listo', 'secondary'], 'queued' => ['En cola', 'primary'],
                                'processing' => ['Procesando', 'primary'],
                                'completed' => $item->product && \App\Models\Product::hasUsableImage($item->product->photo, $item->product->gallery)
                                    ? ['Producto preparado', 'success'] : ['Falta fotografía', 'warning'],
                                'not_found' => ['No encontrado', 'danger'], 'failed' => ['Error', 'danger'],
                                'skipped' => ['Omitido', 'secondary'], 'missing' => ['SKU inexistente', 'danger'],
                            ][$item->status] ?? [$item->status, 'secondary'];
                        @endphp
                        @php
                            $consultationVariants = collect($item->source_skus ?: [$item->submitted_sku])->filter()->unique()->map(function ($sku) use ($consultationProducts) {
                                $product = $consultationProducts->get(mb_strtolower((string) $sku));
                                return ['sku' => (string) $sku, 'name' => $product?->external_name ?: $product?->name ?: ''];
                            })->values();
                        @endphp
                        <tr data-batch-item="{{ $item->id }}">
                            <td class="wa-select-cell" hidden><input type="checkbox" class="wa-item" aria-label="Seleccionar {{ $item->product?->name ?: $item->submitted_sku }}" data-variants="{{ $consultationVariants->toJson() }}"></td>
                            <td><strong>{{ $item->product?->name ?: $item->product?->external_name ?: 'Sin producto' }}</strong><small class="d-block text-muted">{{ $item->product?->sku ?: $item->submitted_sku }}</small></td>
                            <td class="small">{{ implode(', ', $item->source_skus ?: [$item->submitted_sku]) }}</td>
                            <td><span class="badge text-bg-{{ $statusPresentation[1] }} item-status">{{ $statusPresentation[0] }}</span></td>
                            <td class="small text-muted">
                                <div class="item-message">{{ $item->message }}</div>
                                <div class="item-sources">
                                    @include('admin.products.ai-batches.sources', ['sources' => $item->product?->aiPreparation?->sources ?: []])
                                </div>
                            </td>
                            <td>@if($item->product)<a href="{{ route('admin.products.edit', $item->product_id) }}" class="btn btn-sm btn-outline-dark">Abrir</a>@endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin.card>

{{-- TODO: Portal privado de validación por sector con tokens seguros y vencimiento, revisión de fotos, correcciones y aprobación de E-commerce. --}}
<div class="modal fade" id="waConsultationModal" tabindex="-1" aria-labelledby="waModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title fs-5" id="waModalTitle">Consulta por WhatsApp</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body">
            <fieldset class="mb-3" id="waBuildings">
                <legend class="form-label fs-6">Edificio</legend>
                <div class="d-flex flex-wrap gap-3">
                    <div class="form-check"><input class="form-check-input" type="radio" name="waBuilding" id="waBuildingDepartment" value="department" checked><label class="form-check-label" for="waBuildingDepartment">SAX Department Store</label></div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="waBuilding" id="waBuildingDubai" value="dubai"><label class="form-check-label" for="waBuildingDubai">SAX Shopping Dubai</label></div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="waBuilding" id="waBuildingDistrito" value="distrito"><label class="form-check-label" for="waBuildingDistrito">SAX Distrito (Guembe y Casona)</label></div>
                </div>
            </fieldset>
            <script type="application/json" id="waContacts">@json($guideContacts)</script>
            <div class="mb-3"><label for="waSector" class="form-label">Sector de destino</label><select id="waSector" class="form-select"><option value="">Elegir sector o ingresar número manualmente</option></select></div>
            <div class="mb-3"><label for="waPhone" class="form-label">Número de WhatsApp</label><input id="waPhone" type="tel" class="form-control" placeholder="+595 981 123456" aria-describedby="waPhoneHelp"><div id="waPhoneHelp" class="form-text">Incluí el código de país, por ejemplo +595. Solo es necesario para abrir WhatsApp.</div><div class="invalid-feedback">Ingresá un número válido con código de país (entre 8 y 15 dígitos).</div></div>
            <fieldset class="mb-3" id="waReasons" aria-describedby="waReasonsError">
                <legend class="form-label fs-6">Motivos de consulta</legend>
                <div class="d-flex flex-wrap gap-3">
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="waReasonPhotos" value="photos" checked><label class="form-check-label" for="waReasonPhotos">Fotografías</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="waReasonConfirm" value="confirm" checked><label class="form-check-label" for="waReasonConfirm">Confirmar modelo y variantes</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="waReasonDimensions" value="dimensions"><label class="form-check-label" for="waReasonDimensions">Medidas y dimensiones</label></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="waReasonWeight" value="weight"><label class="form-check-label" for="waReasonWeight">Peso del producto</label></div>
                </div>
                <div id="waReasonsError" class="small text-danger mt-2" role="alert" hidden>Seleccioná al menos un motivo de consulta.</div>
            </fieldset>
            <div class="mb-3" id="waGroupContainer" hidden><label for="waGroup" class="form-label">Grupo de productos</label><select id="waGroup" class="form-select"></select><div class="form-text">La selección original se mantiene. Los mensajes se agrupan según su extensión, procurando mantener juntas las variantes.</div></div>
            <div class="mb-2"><label for="waMessage" class="form-label">Mensaje editable</label><textarea id="waMessage" class="form-control" rows="12" aria-describedby="waMessageLength"></textarea><div id="waMessageLength" class="form-text" role="status" aria-live="polite"></div></div>
            <div id="waFeedback" class="small text-success" role="status" aria-live="polite"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" id="waCopy">Copiar mensaje</button><button type="button" class="btn btn-success" id="waOpen">Abrir WhatsApp</button></div>
    </div></div>
</div>
<script src="{{ asset('js/ai-batch-whatsapp.js') }}?v={{ filemtime(public_path('js/ai-batch-whatsapp.js')) }}" defer></script>

@if($batch->status === 'draft')
    <x-admin.ai-catalog-selector
        mode="append"
        :batch="$batch"
        :existing-skus="$batchSkus"
        :brands="$brands"
        :categories="$categories"
        :subcategories="$subcategories"
    />
@endif

@if($batch->status !== 'draft')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('aiBatchProgress');
    const labels = {
        queued: ['En cola', 'primary'], processing: ['Procesando', 'primary'],
        completed: ['Falta fotografía', 'warning'], not_found: ['No encontrado', 'danger'],
        failed: ['Error', 'danger'], skipped: ['Omitido', 'secondary'], missing: ['SKU inexistente', 'danger']
    };
    async function refreshBatch() {
        try {
            const response = await fetch(root.dataset.statusUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                window.setTimeout(refreshBatch, 8000);
                return;
            }
            const data = await response.json();
            const counts = data.counts || {};
            document.getElementById('countCompleted').textContent = counts.completed || 0;
            document.getElementById('countActive').textContent = (counts.queued || 0) + (counts.processing || 0);
            document.getElementById('countReview').textContent = (counts.not_found || 0) + (counts.failed || 0);
            const bar = document.getElementById('batchProgressBar');
            bar.style.width = data.progress + '%';
            bar.textContent = data.progress + '%';
            data.items.forEach(function (item) {
                const row = document.querySelector('[data-batch-item="' + item.id + '"]');
                if (!row || !labels[item.status]) return;
                const badge = row.querySelector('.item-status');
                const presentation = item.status === 'completed' && item.has_image
                    ? ['Producto preparado', 'success'] : labels[item.status];
                badge.className = 'badge text-bg-' + presentation[1] + ' item-status';
                badge.textContent = presentation[0];
                row.querySelector('.item-message').textContent = item.message || '';
                const sourcesRoot = row.querySelector('.item-sources');
                sourcesRoot.replaceChildren();
                const sources = (Array.isArray(item.sources) ? item.sources : []).filter(source =>
                    source && typeof source.url === 'string' && /^https?:\/\//i.test(source.url));
                if (sources.length) {
                    const heading = document.createElement('strong');
                    heading.className = 'd-block mt-2';
                    heading.textContent = 'Fuentes consultadas';
                    const list = document.createElement('ul');
                    list.className = 'mb-0 ps-3';
                    sources.forEach(source => {
                        const entry = document.createElement('li');
                        const link = document.createElement('a');
                        link.href = source.url;
                        link.target = '_blank';
                        link.rel = 'noopener noreferrer';
                        link.className = 'text-break';
                        link.textContent = source.title || source.url;
                        entry.appendChild(link);
                        list.appendChild(entry);
                    });
                    sourcesRoot.append(heading, list);
                }
            });
            if (data.status !== 'completed') window.setTimeout(refreshBatch, 4000);
            else bar.classList.remove('progress-bar-animated');
        } catch (error) {
            window.setTimeout(refreshBatch, 8000);
        }
    }
    refreshBatch();
});
</script>
@endif
@endsection
