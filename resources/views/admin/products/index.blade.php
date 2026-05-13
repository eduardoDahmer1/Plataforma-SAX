@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.menu_produtos') }}"
        description="Exibindo <span class='text-dark fw-bold'>{{ $products->count() }}</span> de {{ $products->total() }} produtos registrados">
        <x-slot:actions>
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
                <div class="row g-3">
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
                                    $imageUrl = 'https://plataforma.cloudcrow.com.br/storage/uploads/noimage.webp';
                                }
                            } else {
                                $imageUrl = 'https://plataforma.cloudcrow.com.br/storage/uploads/noimage.webp';
                            }
                        @endphp

                        <div class="col-12">
                            <div class="border rounded p-3 d-flex flex-column flex-md-row align-items-center gap-3 hover-shadow transition">
                                <!-- Imagem -->
                                <div class="flex-shrink-0 text-center position-relative" style="width: 150px;">
                                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                                        class="img-fluid rounded shadow-sm"
                                        style="max-height:9em; object-fit:cover; display:block; margin:auto;">
                                    @if (!$product->status)
                                        <span class="position-absolute top-0 start-0 badge bg-danger">{{ __('messages.status_inativo') }}</span>
                                    @endif
                                </div>

                                <!-- Informações -->
                                <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 w-100">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-center text-md-start">
                                            {{ $product->name }}
                                        </h6>
                                        <p class="small text-muted mb-1 text-center text-md-start">
                                            {{ $product->external_name }}
                                        </p>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start mb-2">
                                            <span class="badge bg-secondary">SKU: {{ $product->sku }}</span>
                                            @if ($product->product_role)
                                                <span class="badge {{ $product->product_role === 'P' ? 'bg-primary' : 'bg-dark' }}">
                                                    {{ $product->product_role === 'P' ? 'P' : 'F' }}
                                                </span>
                                            @endif
                                            @if ($product->brand)
                                                <span class="badge bg-dark">{{ $product->brand->name }}</span>
                                            @endif
                                        </div>
                                        <p class="fw-semibold text-success mb-1 text-center text-md-start fs-5">
                                            {{ currency_format($product->price) }}
                                        </p>
                                        <p class="small {{ $product->stock > 0 ? 'text-primary' : 'text-danger' }} mb-2 text-center text-md-start">
                                            <i class="fa {{ $product->stock > 0 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                            {{ $product->stock > 0 ? 'Estoque: ' . $product->stock : 'Sem estoque' }}
                                        </p>
                                    </div>

                                    <!-- Ações -->
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <form action="{{ route('admin.products.toggleStatus', $product->id) }}"
                                            method="POST" class="flex-grow-1">
                                            @csrf
                                            <button class="btn btn-sm w-100 {{ $product->status ? 'btn-success' : 'btn-secondary' }}" type="submit">
                                                <i class="fa {{ $product->status ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                                {{ $product->status ? __('messages.status_ativo') : __('messages.status_inativo') }}
                                            </button>
                                        </form>

                                        <a href="{{ route('admin.products.edit', ['product' => $product->id, 'return_to' => request()->fullUrl()]) }}"
                                            class="btn btn-sm btn-warning flex-grow-1">
                                            <i class="fa fa-edit me-1"></i> {{ __('messages.editar') }}
                                        </a>

                                        <button type="button" class="btn btn-sm btn-info flex-grow-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#highlightsModal{{ $product->id }}">
                                            <i class="fa fa-star me-1"></i> Destaques
                                        </button>

                                        <form action="{{ route('admin.products.destroy', $product->id) }}"
                                            method="POST" class="flex-grow-1">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger w-100"
                                                onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                                <i class="fa fa-trash me-1"></i> Excluir
                                            </button>
                                        </form>
                                    </div>
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
                                                <i class="fa fa-star text-warning me-2"></i>
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
                                            <button type="submit" class="btn btn-primary">
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
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
    @endif
</x-admin.card>
@endsection

@section('styles')

@endsection
