@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.menu_produtos') }}"
        description="Exibindo <span class='text-dark fw-bold'>{{ $products->count() }}</span> de {{ $products->total() }} produtos registrados">
        <x-slot:actions>
            <a href="{{ route('admin.products.ai-batches.index') }}" class="btn btn-outline-primary btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                <i class="fa fa-wand-magic-sparkles me-2"></i> IA em lote
            </a>
            <a href="{{ route('admin.products.outlet.form') }}" class="btn btn-outline-danger btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                <i class="fa fa-box-open me-2"></i> Gestão de outlet
            </a>
            <button type="button" id="btnRevalidateProducts" class="btn btn-outline-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1"
                data-url="{{ route('admin.products.revalidateStatus') }}"
                data-label-active="{{ __('messages.status_ativo') }}"
                data-label-inactive="{{ __('messages.status_inativo') }}">
                <i class="fa fa-sync me-2"></i> Verificar produtos
            </button>
            <a href="{{ route('admin.products.review') }}" class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                <i class="fa fa-file-alt me-2"></i> Ver relatório de edições
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="product-feed-panel mb-3">
        <button
            class="product-feed-panel__toggle collapsed"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#productFeedDetails"
            aria-expanded="false"
            aria-controls="productFeedDetails">
            <span class="product-feed-panel__icon" aria-hidden="true">
                <i class="fa fa-file-code"></i>
            </span>
            <span class="product-feed-panel__summary">
                <span class="product-feed-panel__title">Feed XML de produtos</span>
                <span class="product-feed-panel__meta">
                    <span class="product-feed-panel__status {{ $productFeed['exists'] ? 'is-ready' : '' }} {{ $productFeed['pending'] ? 'is-pending' : '' }}">
                        <i class="fa fa-circle" aria-hidden="true"></i>
                        {{ $productFeed['pending'] ? 'Atualizando automaticamente' : ($productFeed['exists'] ? 'Disponível e sincronizado' : 'Não gerado') }}
                    </span>
                    @if ($productFeed['exists'] && $productFeed['count'] !== null)
                        <span>{{ number_format($productFeed['count'], 0, ',', '.') }} produto(s)</span>
                    @endif
                </span>
            </span>
            <span class="product-feed-panel__action">
                <span>Gerenciar</span>
                <i class="fa fa-chevron-down product-feed-panel__chevron" aria-hidden="true"></i>
            </span>
        </button>

        <div class="collapse" id="productFeedDetails">
            <div class="product-feed-panel__body">
                <div class="d-flex flex-column flex-xl-row align-items-xl-end justify-content-between gap-3">
                    <div class="flex-grow-1 min-w-0">
                        <label for="productFeedUrl" class="form-label small fw-bold text-uppercase mb-1">Link público permanente</label>
                        <p class="small text-muted mb-2">Produtos ativados, desativados ou editados atualizam este XML automaticamente, sem alterar a URL.</p>
                        <div class="input-group product-feed-panel__link">
                            <input type="text" id="productFeedUrl" class="form-control bg-white" value="{{ $productFeed['url'] }}" readonly aria-label="Link público do XML de produtos">
                            <button type="button" class="btn btn-outline-secondary" id="copyProductFeedUrl" title="Copiar link">
                                <i class="far fa-copy me-1"></i> Copiar
                            </button>
                            @if ($productFeed['exists'])
                                <a href="{{ $productFeed['url'] }}" target="_blank" rel="noopener" class="btn btn-outline-secondary" title="Abrir XML em uma nova guia">
                                    <i class="fa fa-arrow-up-right-from-square me-1"></i> Abrir
                                </a>
                            @endif
                        </div>
                        @if ($productFeed['generated_at'])
                            <div class="product-feed-panel__updated">
                                <i class="far fa-clock me-1" aria-hidden="true"></i>
                                Atualizado em {{ \Carbon\Carbon::parse($productFeed['generated_at'])->timezone(config('app.timezone'))->format('d/m/Y \à\s H:i:s') }}
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.products.feed.generate') }}" class="flex-shrink-0" id="generateProductFeedForm">
                        @csrf
                        <button type="submit" class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                            <i class="fa fa-rotate me-2"></i>
                            {{ $productFeed['exists'] ? 'Gerar XML novo' : 'Gerar XML' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .product-feed-panel {
                overflow: hidden;
                border: 1px solid #e3e8f0;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
            }
            .product-feed-panel__toggle {
                width: 100%;
                min-height: 64px;
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 16px;
                border: 0;
                background: #fff;
                color: #172033;
                text-align: left;
                transition: background-color .18s ease;
            }
            .product-feed-panel__toggle:hover,
            .product-feed-panel__toggle:focus-visible {
                background: #f8fafc;
            }
            .product-feed-panel__toggle:focus-visible {
                outline: 2px solid #2563eb;
                outline-offset: -2px;
            }
            .product-feed-panel__icon {
                width: 38px;
                height: 38px;
                flex: 0 0 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 9px;
                background: #172033;
                color: #fff;
            }
            .product-feed-panel__summary {
                min-width: 0;
                display: flex;
                flex: 1;
                flex-direction: column;
                gap: 2px;
            }
            .product-feed-panel__title {
                font-size: .94rem;
                font-weight: 700;
                line-height: 1.2;
            }
            .product-feed-panel__meta {
                display: flex;
                align-items: center;
                gap: 9px;
                color: #7a8497;
                font-size: .75rem;
            }
            .product-feed-panel__status {
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }
            .product-feed-panel__status .fa-circle {
                color: #94a3b8;
                font-size: 6px;
            }
            .product-feed-panel__status.is-ready .fa-circle {
                color: #16a34a;
            }
            .product-feed-panel__status.is-pending {
                color: #9a6700;
            }
            .product-feed-panel__status.is-pending .fa-circle {
                color: #f59e0b;
                animation: product-feed-pulse 1.4s ease-in-out infinite;
            }
            @keyframes product-feed-pulse {
                50% { opacity: .35; transform: scale(.8); }
            }
            .product-feed-panel__action {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                color: #64748b;
                font-size: .72rem;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            .product-feed-panel__chevron {
                transition: transform .2s ease;
            }
            .product-feed-panel__toggle:not(.collapsed) .product-feed-panel__chevron {
                transform: rotate(180deg);
            }
            .product-feed-panel__body {
                padding: 16px;
                border-top: 1px solid #e8ecf2;
                background: #f8fafc;
            }
            .product-feed-panel__link {
                max-width: 760px;
            }
            .product-feed-panel__link .form-control,
            .product-feed-panel__link .btn {
                min-height: 42px;
            }
            .product-feed-panel__updated {
                margin-top: 8px;
                color: #7a8497;
                font-size: .75rem;
            }
            @media (max-width: 575.98px) {
                .product-feed-panel__toggle {
                    min-height: 58px;
                    padding: 9px 12px;
                }
                .product-feed-panel__action span {
                    display: none;
                }
                .product-feed-panel__body {
                    padding: 14px 12px;
                }
                .product-feed-panel__link {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                }
                .product-feed-panel__link .form-control {
                    width: 100%;
                    grid-column: 1 / -1;
                    border-radius: 6px 6px 0 0 !important;
                }
                .product-feed-panel__link .btn {
                    border-radius: 0 0 6px 6px;
                }
            }
        </style>
    @endpush

    @include('admin.products.index-componentes.form')

    <x-admin.alert />

    @push('scripts')
        <script>
            document.getElementById('copyProductFeedUrl')?.addEventListener('click', async function () {
                const input = document.getElementById('productFeedUrl');
                try {
                    await navigator.clipboard.writeText(input.value);
                } catch (error) {
                    input.select();
                    document.execCommand('copy');
                }

                const original = this.innerHTML;
                this.innerHTML = '<i class="fa fa-check me-1"></i> Copiado';
                window.setTimeout(() => { this.innerHTML = original; }, 1800);
            });

            document.getElementById('generateProductFeedForm')?.addEventListener('submit', function () {
                const button = this.querySelector('button[type="submit"]');
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span> Gerando XML...';
            });
        </script>
    @endpush

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($products->isEmpty())
                <div class="text-center py-5">
                    <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Nenhum produto encontrado com os filtros selecionados.</p>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-primary">Ver todos os produtos</a>
                </div>
            @else
                <div class="sax-admin-list">
                    @foreach ($products as $product)
                        @php
                            $highlightsValues = json_decode($product->highlights ?? '{}', true);
                            $createdAt = $product->created_at;
                            $lastEditedAt = $product->admin_edited_at ?? $product->updated_at;
                            $lastEditedBy = $product->editor?->name ?? __('messages.product_audit_system_integration');
                            $aiDisplayStatus = $product->productAiDisplayStatus();
                            $aiStatusPresentation = [
                                'prepared' => ['Produto preparado', 'success'],
                                'missing_photo' => ['Falta fotografia', 'warning'],
                                'review' => ['Requer revisão', 'danger'],
                                'pending' => ['Pendente', 'secondary'],
                            ][$aiDisplayStatus];
                            $dhlMeasurement = $product->dhl_shipping_measurement;

                            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                                $imageUrl = asset('storage/' . $product->photo);
                            } elseif ($product->gallery) {
                                $gallery = is_array($product->gallery)
                                    ? $product->gallery
                                    : json_decode($product->gallery, true);
                                $imageUrl = null;
                                foreach ($gallery as $img) {
                                    if (Storage::disk('public')->exists($img)) {
                                        $imageUrl = asset('storage/' . $img);
                                        break;
                                    }
                                }
                                if (!$imageUrl) {
                                    $imageUrl = asset('storage/uploads/noimage.webp');
                                }
                            } else {
                                $imageUrl = asset('storage/uploads/noimage.webp');
                            }
                        @endphp

                        <div class="sax-product-card mb-2" id="product-card-{{ $product->id }}">
                            <div class="row align-items-center g-2 g-md-0">
                                <!-- Imagem -->
                                <div class="col-auto">
                                    <div class="sax-product-img-box">
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name ?: $product->external_name }}"
                                            class="{{ $product->status ? '' : 'is-inactive' }}">
                                    </div>
                                </div>

                                <!-- Informações -->
                                <div class="col ps-3 min-w-0">
                                    <div class="mb-1 d-flex flex-wrap align-items-center gap-2">
                                        <span class="badge bg-light text-muted border rounded-0 x-small-7 fw-bold">SKU: {{ $product->sku }}</span>
                                        @if ($product->product_role)
                                            <span class="badge bg-light text-muted border rounded-0 x-small-7 fw-bold">
                                                {{ $product->product_role === 'P' ? 'P' : 'F' }}
                                            </span>
                                        @endif
                                        @if ($product->brand)
                                            <span class="badge bg-light text-muted border rounded-0 x-small-7 fw-bold">{{ $product->brand->name }}</span>
                                        @endif
                                        <span class="product-status-pill {{ $product->status ? 'is-on' : 'is-off' }}">
                                            {{ $product->status ? __('messages.status_ativo') : __('messages.status_inativo') }}
                                        </span>
                                        <span class="badge text-bg-{{ $aiStatusPresentation[1] }} rounded-0 x-small-7 fw-bold">
                                            {{ $aiStatusPresentation[0] }}
                                        </span>
                                        @if ($product->is_outlet)
                                            <span class="badge bg-danger text-white rounded-0 x-small-7 fw-bold">OUTLET · FORA DO E-COMMERCE</span>
                                        @endif
                                    </div>
                                    <h6 class="fw-bold mb-0 small text-truncate">{{ $product->name ?: $product->external_name }}</h6>
                                    @if($product->external_name && trim($product->external_name) !== trim((string) $product->name))
                                        <p class="x-small text-muted mb-1 text-truncate" title="Nome original do Sistema GO">{{ $product->external_name }}</p>
                                    @endif
                                    <div class="d-flex align-items-center gap-2 x-small fw-bold text-dark">
                                        <span>{{ currency_format($product->price) }}</span>
                                        <span class="text-muted">•</span>
                                        <span class="text-muted fw-normal">
                                            {{ $product->stock > 0 ? 'Estoque: ' . $product->stock : 'Sem estoque' }}
                                        </span>
                                    </div>
                                    <div class="x-small text-muted mt-1 text-truncate"
                                        title="{{ $dhlMeasurement['estimated'] ? __('messages.admin_dhl_measurement_estimated_title', ['source' => $dhlMeasurement['label']]) : __('messages.admin_dhl_measurement_exact_title') }}">
                                        <i class="fa-solid fa-ruler-combined me-1" aria-hidden="true"></i>
                                        {{ __('messages.admin_dhl_measurement_summary', [
                                            'weight' => number_format($dhlMeasurement['weight'], 3, ',', '.'),
                                            'length' => number_format($dhlMeasurement['length'], 1, ',', '.'),
                                            'width' => number_format($dhlMeasurement['width'], 1, ',', '.'),
                                            'height' => number_format($dhlMeasurement['height'], 1, ',', '.'),
                                        ]) }}
                                        @if ($dhlMeasurement['restricted'])
                                            <span class="badge bg-warning-subtle text-warning-emphasis border ms-1">Revisão</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 x-small text-muted mt-1">
                                        <span title="{{ __('messages.product_audit_created_title') }}">
                                            <i class="far fa-calendar-plus me-1"></i>
                                            {{ __('messages.product_audit_created_at', ['date' => $createdAt?->format('d/m/Y \à\s H:i') ?? __('messages.product_audit_date_not_registered')]) }}
                                        </span>
                                        <span class="d-none d-sm-inline">•</span>
                                        <span title="{{ __('messages.product_audit_datetime_title') }}">
                                            <i class="far fa-clock me-1"></i>
                                            {{ __('messages.product_audit_updated_at', ['date' => $lastEditedAt?->format('d/m/Y \à\s H:i') ?? __('messages.product_audit_date_not_registered')]) }}
                                        </span>
                                        <span class="d-none d-sm-inline">•</span>
                                        <span title="{{ __('messages.product_audit_responsible_title') }}">
                                            <i class="far fa-user me-1"></i>
                                            {{ __('messages.product_audit_by', ['name' => $lastEditedBy]) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Ações -->
                                <div class="col-12 col-md-auto sax-product-actions">
                                    <button type="button" class="action-icon action-icon-lg btn-toggle-status {{ $product->status ? 'is-on' : '' }}"
                                        title="Ativar/Desativar"
                                        {{ $product->is_outlet ? 'disabled' : '' }}
                                        data-url="{{ route('admin.products.toggleStatus', $product->id) }}"
                                        data-label-active="{{ __('messages.status_ativo') }}"
                                        data-label-inactive="{{ __('messages.status_inativo') }}">
                                        <i class="fa {{ $product->status ? 'fa-toggle-on' : 'fa-toggle-off' }} icon-toggle"></i>
                                    </button>

                                    <button type="button" class="action-icon action-icon-lg" title="Destaques"
                                        data-bs-toggle="modal" data-bs-target="#highlightsModal{{ $product->id }}">
                                        <i class="far fa-star"></i>
                                    </button>

                                    <a href="{{ route('admin.products.edit', ['product' => $product->id, 'return_to' => request()->fullUrl()]) }}"
                                        class="action-icon action-icon-lg" title="{{ __('messages.editar') }}">
                                        <i class="far fa-edit"></i>
                                    </a>

                                    <button type="button" class="action-icon action-icon-lg btn-delete-product" title="Excluir"
                                        data-url="{{ route('admin.products.destroy', $product->id) }}"
                                        data-product-id="{{ $product->id }}">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de destaques -->
                        <div class="modal fade" id="highlightsModal{{ $product->id }}" tabindex="-1"
                            aria-labelledby="highlightsModalLabel{{ $product->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <form class="form-highlights"
                                    action="{{ route('admin.products.updateHighlights', $product->id) }}"
                                    method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="far fa-star me-2"></i>
                                                Destaques do Produto
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @php
                                                $highlights = [
                                                    'destaque' => 'Destaques',
                                                    'lancamentos' => __('messages.lancamentos'),
                                                ];
                                            @endphp
                                            <div class="row">
                                                @foreach ($highlights as $key => $label)
                                                    <div class="col-12 col-md-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="highlights[{{ $key }}]"
                                                                id="{{ $key }}{{ $product->id }}"
                                                                value="1"
                                                                {{ !empty($highlightsValues[$key]) ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="{{ $key }}{{ $product->id }}">
                                                                {{ $label }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Fechar
                                            </button>
                                            <button type="submit" class="btn btn-dark">
                                                <i class="fa fa-save me-1"></i> Salvar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Paginação -->
    @if ($products->hasPages())
        <div class="d-flex justify-content-center mt-4 pagination-sax">
            {{ $products->links() }}
        </div>
    @endif
</x-admin.card>
@endsection

@section('styles')

@endsection
