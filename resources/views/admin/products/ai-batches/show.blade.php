@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Lote de IA #{{ $batch->id }}"
        description="{{ $batch->original_filename ?: 'Códigos pegados manualmente' }} · creado por {{ $batch->creator?->name ?: 'Administrador' }}">
        <x-slot:actions>
            <a href="{{ route('admin.products.ai-batches.index') }}" class="btn btn-outline-dark btn-sax-lg px-4">
                <i class="fa fa-arrow-left me-2"></i> Nuevo lote
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />
    @error('batch')<div class="alert alert-danger">{{ $message }}</div>@enderror

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
                @if($batch->eligible_count > 0)
                    <form method="POST" action="{{ route('admin.products.ai-batches.dispatch', $batch) }}">
                        @csrf
                        <button class="btn btn-primary fw-bold" onclick="this.disabled=true; this.form.submit();">
                            <i class="fa fa-play me-2"></i> Generar información
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="progress mb-4" style="height: 22px;">
                <div id="batchProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Producto</th><th>Códigos recibidos</th><th>Estado</th><th>Detalle</th><th></th></tr></thead>
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
                        <tr data-batch-item="{{ $item->id }}">
                            <td><strong>{{ $item->product?->name ?: $item->product?->external_name ?: 'Sin producto' }}</strong><small class="d-block text-muted">{{ $item->product?->sku ?: $item->submitted_sku }}</small></td>
                            <td class="small">{{ implode(', ', $item->source_skus ?: [$item->submitted_sku]) }}</td>
                            <td><span class="badge text-bg-{{ $statusPresentation[1] }} item-status">{{ $statusPresentation[0] }}</span></td>
                            <td class="small text-muted item-message">{{ $item->message }}</td>
                            <td>@if($item->product)<a href="{{ route('admin.products.edit', $item->product_id) }}" class="btn btn-sm btn-outline-dark">Abrir</a>@endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin.card>

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
