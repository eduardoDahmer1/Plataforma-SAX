@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Generación masiva con IA"
        description="Prepará hasta 1.000 SKU, agrupados en un máximo de 100 generaciones de IA.">
        <x-slot:actions>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark btn-sax-lg px-4">
                <i class="fa fa-arrow-left me-2"></i> Volver a productos
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-xl-5">
                    <h5 class="fw-bold mb-1">Crear un lote</h5>
                    <p class="text-muted mb-4">La vista previa no consume IA. Podrás confirmar después de revisar los códigos encontrados.</p>

                    <form method="POST" action="{{ route('admin.products.ai-batches.preview') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="aiBatchFile" class="form-label fw-bold">Archivo Excel (.xlsx)</label>
                            <input id="aiBatchFile" type="file" name="file" accept=".xlsx" class="form-control @error('file') is-invalid @enderror">
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Debe incluir una columna código, codigo, sku o referencia. Máximo 1.000 SKU únicos.</div>
                        </div>

                        <div class="d-flex align-items-center gap-3 my-4">
                            <hr class="flex-grow-1"><span class="text-muted small fw-bold">O PEGÁ LOS CÓDIGOS</span><hr class="flex-grow-1">
                        </div>

                        <div class="mb-4">
                            <label for="aiBatchSkus" class="form-label fw-bold">Lista de códigos</label>
                            <textarea id="aiBatchSkus" name="skus" rows="10" class="form-control @error('skus') is-invalid @enderror"
                                placeholder="Un código por línea. También se aceptan comas y punto y coma.">{{ old('skus') }}</textarea>
                            @error('skus')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex align-items-center gap-3 my-4">
                            <hr class="flex-grow-1"><span class="text-muted small fw-bold">O SELECCIONÁ DEL CATÁLOGO</span><hr class="flex-grow-1">
                        </div>

                        <div class="mb-4">
                            <button type="button" class="btn btn-outline-dark w-100 py-3 fw-bold"
                                data-bs-toggle="modal" data-bs-target="#aiCatalogModal">
                                <i class="fa fa-boxes-stacked me-2"></i> Seleccionar productos del catálogo
                            </button>
                            <div class="form-text">Buscá productos por SKU, nombre o referencia y filtrá el catálogo sin salir de esta pantalla.</div>
                            <div id="aiCatalogFormFeedback" class="form-text text-success fw-bold d-none" aria-live="polite"></div>
                        </div>

                        <button type="submit" class="btn btn-dark px-5 py-3 fw-bold">
                            <i class="fa fa-magnifying-glass me-2"></i> Validar y ver resumen
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="alert alert-light border rounded-4 p-4 mb-4">
                <h6 class="fw-bold"><i class="fa fa-shield-halved me-2"></i>Proceso seguro</h6>
                <p class="small text-muted mb-0">La IA guarda la información, pero no activa productos. Los ya preparados o publicados se omiten automáticamente.</p>
            </div>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Lotes recientes</h6>
                    @forelse($batches as $batch)
                        <a href="{{ route('admin.products.ai-batches.show', $batch) }}" class="d-flex justify-content-between align-items-center text-decoration-none border-bottom py-3">
                            <span>
                                <strong class="text-dark">Lote #{{ $batch->id }}</strong>
                                <small class="d-block text-muted">{{ $batch->created_at->format('d/m/Y H:i') }} · {{ $batch->input_count }} códigos</small>
                            </span>
                            <span class="badge {{ $batch->status === 'completed' ? 'text-bg-success' : ($batch->status === 'draft' ? 'text-bg-secondary' : 'text-bg-primary') }}">{{ $batch->status }}</span>
                        </a>
                    @empty
                        <p class="text-muted mb-0">Todavía no hay lotes.</p>
                    @endforelse
                    <div class="mt-3">{{ $batches->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-admin.card>

<x-admin.ai-catalog-selector
    mode="create"
    :brands="$brands"
    :categories="$categories"
    :subcategories="$subcategories"
    :existing-skus="[]"
/>
@endsection
