@extends('layout.admin')

@section('content')
    <div class="container my-4">
        <!-- Contador -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
            <h1 class="mb-2 mb-md-0">Produtos</h1>
            <a href="{{ route('admin.products.review') }}" class="btn btn-primary fw-bold">
                Ver relatório de edições
            </a>            
            <p><strong>Exibindo:</strong> {{ $products->count() }} de {{ $products->total() }} registros</p>
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
                        <option value="{{ $category->slug }}" {{ request('category_slug') == $category->slug ? 'selected' : '' }}>
                            {{ $category->slug }}
                        </option>
                    @endforeach
                </select>

                <!-- Status + Estoque -->
                <select name="status_filter" class="form-select">
                    <option value="">Todos os produtos</option>
                    <option value="active" {{ request('status_filter') == 'active' ? 'selected' : '' }}>Produtos Ativos
                    </option>
                    <option value="inactive" {{ request('status_filter') == 'inactive' ? 'selected' : '' }}>Produtos
                        Inativos</option>
                    <option value="with_image" {{ request('status_filter') == 'with_image' ? 'selected' : '' }}>Com Imagem
                    </option>
                    <option value="without_image" {{ request('status_filter') == 'without_image' ? 'selected' : '' }}>Sem
                        Imagem</option>
                    <option value="in_stock" {{ request('status_filter') == 'in_stock' ? 'selected' : '' }}>Com Estoque
                    </option>
                    <option value="out_of_stock" {{ request('status_filter') == 'out_of_stock' ? 'selected' : '' }}>Sem
                        Estoque</option>
                </select>

                <!-- Ordenação -->
                <select name="sort_by" class="form-select">
                    <option value="">Ordenar por...</option>
                    <option value="latest" {{ request('sort_by') == 'latest' ? 'selected' : '' }}>Últimos adicionados
                    </option>
                    <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>Primeiros adicionados
                    </option>
                    <option value="recently_updated" {{ request('sort_by') == 'recently_updated' ? 'selected' : '' }}>
                        Últimos editados</option>
                    <option value="old_updated" {{ request('sort_by') == 'old_updated' ? 'selected' : '' }}>Primeiros
                        editados</option>
                    <option value="price_low" {{ request('sort_by') == 'price_low' ? 'selected' : '' }}>Menor preço
                    </option>
                    <option value="price_high" {{ request('sort_by') == 'price_high' ? 'selected' : '' }}>Maior preço
                    </option>
                    <option value="name_az" {{ request('sort_by') == 'name_az' ? 'selected' : '' }}>Nome (A–Z)</option>
                    <option value="name_za" {{ request('sort_by') == 'name_za' ? 'selected' : '' }}>Nome (Z–A)</option>
                </select>


                <!-- Destaques -->
                <select name="highlight_filter" class="form-select">
                    <option value="">Todos os destaques</option>
                    @foreach ($highlights as $key => $label)
                        <option value="{{ $key }}" {{ request('highlight_filter') == $key ? 'selected' : '' }}>
                            {{ $label }}</option>
                    @endforeach
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

                            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                                $imageUrl = asset('storage/' . $product->photo);
                            } elseif ($product->gallery) {
                                $gallery = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery, true);
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

                        <div class="mb-3">
                            <div class="border rounded p-3 d-flex flex-column flex-md-row align-items-center gap-3">
                                <!-- Imagem -->
                                <div class="flex-shrink-0 text-center" style="width: 150px;">
                                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                                        class="img-fluid rounded"
                                        style="max-height:9em; object-fit:cover; display:block; margin:auto;">
                                </div>

                                <!-- Informações -->
                                <div class="flex-grow-1 d-flex flex-column justify-content-between h-100 w-100">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-center text-md-start">{{ $product->name }}
                                        <p class="small text-muted mb-1 text-center text-md-start">{{ $product->external_name }}</p>
                                        <p class="small text-muted mb-1 text-center text-md-start">
                                            SKU: {{ $product->sku }}
                                        </p>
                                        @if ($product->product_role)
                                            <p class="small text-muted mb-1 text-center text-md-start">
                                                Tipo: {{ $product->product_role === 'P' ? 'Pai (P)' : 'Filho (F)' }}
                                            </p>
                                        @endif

                                        <p class="fw-semibold text-success mb-1 text-center text-md-start">
                                            {{ currency_format($product->price) }}
                                        </p>
                                        <p
                                            class="small {{ $product->stock > 0 ? 'text-primary' : 'text-danger' }} mb-2 text-center text-md-start">
                                            {{ $product->stock > 0 ? 'Estoque: ' . $product->stock : 'Sem estoque' }}
                                        </p>
                                    </div>


                                    <!-- Ações -->
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <form action="{{ route('admin.products.toggleStatus', $product->id) }}"
                                            method="POST" class="flex-grow-1">
                                            @csrf
                                            <button
                                                class="btn btn-sm w-100 {{ $product->status ? 'btn-success' : 'btn-secondary' }}"
                                                type="submit">
                                                {{ $product->status ? 'Ativo' : 'Inativo' }}
                                            </button>
                                        </form>

                                        <a href="{{ route('admin.products.edit', $product->id) }}"
                                            class="btn btn-sm btn-warning flex-grow-1">
                                            <i class="fa fa-edit me-1"></i> Editar
                                        </a>

                                        <button type="button" class="btn btn-sm btn-info flex-grow-1"
                                            data-bs-toggle="modal" data-bs-target="#highlightsModal{{ $product->id }}">
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
                                        action="{{ route('admin.products.updateHighlights', $product->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Destaques do Produto</h5>
                                                <button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                @php
                                                    $highlights = [
                                                        'destaque' => 'Destaques',
                                                        'mais_vendidos' => 'Mais Vendidos',
                                                        'melhores_avaliacoes' => 'Melhores Avaliações',
                                                        'super_desconto' => 'Super Desconto',
                                                        'famosos' => 'Famosos',
                                                        'lancamentos' => 'Lançamentos',
                                                        'tendencias' => 'Tendências',
                                                        'promocoes' => 'Promoções',
                                                        'ofertas_relampago' => 'Ofertas Relâmpago',
                                                        'navbar' => 'Navbar',
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
