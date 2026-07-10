@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.menu_produtos') }}"
        description="Exibindo <span class='text-dark fw-bold'>{{ $products->count() }}</span> de {{ $products->total() }} produtos registrados">
        <x-slot:actions>
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

    @include('admin.products.index-componentes.form')

    <x-admin.alert />

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
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
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
                                    </div>
                                    <h6 class="fw-bold mb-0 small text-truncate">{{ $product->name }}</h6>
                                    <p class="x-small text-muted mb-1 text-truncate">{{ $product->external_name }}</p>
                                    <div class="d-flex align-items-center gap-2 x-small fw-bold text-dark">
                                        <span>{{ currency_format($product->price) }}</span>
                                        <span class="text-muted">•</span>
                                        <span class="text-muted fw-normal">
                                            {{ $product->stock > 0 ? 'Estoque: ' . $product->stock : 'Sem estoque' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Ações -->
                                <div class="col-12 col-md-auto sax-product-actions">
                                    <button type="button" class="action-icon action-icon-lg btn-toggle-status {{ $product->status ? 'is-on' : '' }}"
                                        title="Ativar/Desativar"
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
