@extends('layout.admin')

@section('content')
    <div class="container my-4">
        <!-- Contador -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
            <h1 class="mb-2 mb-md-0">Produtos</h1>
            <p><strong>Exibindo:</strong> {{ $products->count() }} de {{ $products->total() }} registros </p>
        </div>

        <!-- Formulário de busca e filtros -->
        <form action="{{ route('admin.products.index') }}" method="GET" class="mb-4">
            <div class="input-group flex-column flex-md-row mb-3">
                <input type="text" name="search" class="form-control mb-2 mb-md-0"
                    placeholder="Buscar por nome, SKU ou slug" value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">Buscar</button>
            </div>

            <div class="d-flex flex-column flex-md-row gap-2">
                <!-- Marcas -->
                <select name="brand_id" class="form-select">
                    <option value="">Todas as marcas</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            
                <!-- Categorias -->
                <select name="category_id" class="form-select">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            
                <!-- Status + Estoque -->
                <select name="status_filter" class="form-select">
                    <option value="">Todos os produtos</option>
                    <option value="active" {{ request('status_filter') == 'active' ? 'selected' : '' }}>Produtos Ativos</option>
                    <option value="inactive" {{ request('status_filter') == 'inactive' ? 'selected' : '' }}>Produtos Inativos</option>
                    <option value="with_image" {{ request('status_filter') == 'with_image' ? 'selected' : '' }}>Com Imagem</option>
                    <option value="without_image" {{ request('status_filter') == 'without_image' ? 'selected' : '' }}>Sem Imagem</option>
                    <option value="in_stock" {{ request('status_filter') == 'in_stock' ? 'selected' : '' }}>Com Estoque</option>
                    <option value="out_of_stock" {{ request('status_filter') == 'out_of_stock' ? 'selected' : '' }}>Sem Estoque</option>
                </select>
            
                <!-- Destaques -->
                <select name="highlight_filter" class="form-select">
                    <option value="">Todos os destaques</option>
                    <option value="destaque" {{ request('highlight_filter') == 'destaque' ? 'selected' : '' }}>Destaques</option>
                    <option value="mais_vendidos" {{ request('highlight_filter') == 'mais_vendidos' ? 'selected' : '' }}>Mais Vendidos</option>
                    <option value="melhores_avaliacoes" {{ request('highlight_filter') == 'melhores_avaliacoes' ? 'selected' : '' }}>Melhores Avaliações</option>
                    <option value="super_desconto" {{ request('highlight_filter') == 'super_desconto' ? 'selected' : '' }}>Super Desconto</option>
                    <option value="famosos" {{ request('highlight_filter') == 'famosos' ? 'selected' : '' }}>Famosos</option>
                    <option value="lancamentos" {{ request('highlight_filter') == 'lancamentos' ? 'selected' : '' }}>Lançamentos</option>
                    <option value="tendencias" {{ request('highlight_filter') == 'tendencias' ? 'selected' : '' }}>Tendências</option>
                    <option value="promocoes" {{ request('highlight_filter') == 'promocoes' ? 'selected' : '' }}>Promoções</option>
                    <option value="ofertas_relampago" {{ request('highlight_filter') == 'ofertas_relampago' ? 'selected' : '' }}>Ofertas Relâmpago</option>
                    <option value="navbar" {{ request('highlight_filter') == 'navbar' ? 'selected' : '' }}>Navbar</option>
                </select>
            </div>
        </form>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($products as $product)
                        @php
                            $highlightsValues = json_decode($product->highlights ?? '{}', true);
                        @endphp

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                                <!-- Nome e SKU -->
                                <div class="mb-2">
                                    <h6 class="fw-bold mb-1">{{ $product->external_name }}</h6>
                                    <p class="small text-muted mb-0">SKU: {{ $product->sku }}</p>
                                </div>

                                <!-- Preço -->
                                <p class="fw-semibold text-success mb-1">
                                    {{ currency_format($product->price) }}
                                </p>

                                <!-- Estoque -->
                                <p class="small {{ $product->stock > 0 ? 'text-primary' : 'text-danger' }} mb-2">
                                    {{ $product->stock > 0 ? 'Estoque: ' . $product->stock : 'Sem estoque' }}
                                </p>

                                <!-- Status -->
                                <form action="{{ route('admin.products.toggleStatus', $product->id) }}" method="POST"
                                    class="mb-3">
                                    @csrf
                                    <button
                                        class="btn btn-sm w-100 {{ $product->status ? 'btn-success' : 'btn-secondary' }}"
                                        type="submit">
                                        {{ $product->status ? 'Ativo' : 'Inativo' }}
                                    </button>
                                </form>

                                <!-- Ações -->
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('admin.products.edit', $product->id) }}"
                                        class="btn btn-sm btn-warning flex-grow-1">
                                        <i class="fa fa-edit me-1"></i> Editar
                                    </a>

                                    <button type="button" class="btn btn-sm btn-info flex-grow-1" data-bs-toggle="modal"
                                        data-bs-target="#highlightsModal{{ $product->id }}">
                                        <i class="fa fa-star me-1"></i> Destaques
                                    </button>

                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                        class="flex-grow-1">
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

                        <!-- Modal de destaques -->
                        <div class="modal fade" id="highlightsModal{{ $product->id }}" tabindex="-1"
                            aria-labelledby="highlightsModalLabel{{ $product->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <form class="form-highlights"
                                    action="{{ route('admin.products.updateHighlights', $product->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Destaques do Produto</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @php
                                                $highlights = [
                                                    'destaque' => 'Exibir em Destaques',
                                                    'mais_vendidos' => 'Exibir em Mais Vendidos',
                                                    'melhores_avaliacoes' => 'Exibir em Melhores Avaliações',
                                                    'super_desconto' => 'Exibir em Super Desconto',
                                                    'famosos' => 'Exibir em Famosos',
                                                    'lancamentos' => 'Exibir em Lançamentos',
                                                    'tendencias' => 'Exibir em Tendências',
                                                    'promocoes' => 'Exibir em Promoções',
                                                    'ofertas_relampago' => 'Exibir em Ofertas Relâmpago',
                                                    'navbar' => 'Exibir em Navbar',
                                                ];
                                            @endphp
                                            @foreach ($highlights as $key => $label)
                                                <div class="form-check text-start">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="highlights[{{ $key }}]"
                                                        id="{{ $key }}{{ $product->id }}" value="1"
                                                        {{ !empty($highlightsValues[$key]) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="{{ $key }}{{ $product->id }}">{{ $label }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Fechar</button>
                                            <button type="submit" class="btn btn-primary">Salvar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-3">
            {{ $products->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.form-highlights').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alert(result.message);
                    const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                    modal.hide();
                }
            });
        });
    </script>
@endsection
