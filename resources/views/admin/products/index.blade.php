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

    @include('admin.products.index-componentes.form')

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

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
                                <div
                                    class="border rounded p-3 d-flex flex-column flex-md-row align-items-center gap-3 hover-shadow transition">
                                    <!-- Imagem -->
                                    <div class="flex-shrink-0 text-center position-relative" style="width: 150px;">
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                                            class="img-fluid rounded shadow-sm"
                                            style="max-height:9em; object-fit:cover; display:block; margin:auto;">
                                        @if (!$product->status)
                                            <span class="position-absolute top-0 start-0 badge bg-danger">Inativo</span>
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
                                            <div
                                                class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start mb-2">
                                                <span class="badge bg-secondary">SKU: {{ $product->sku }}</span>
                                                @if ($product->product_role)
                                                    <span
                                                        class="badge {{ $product->product_role === 'P' ? 'bg-primary' : 'bg-info' }}">
                                                        {{ $product->product_role === 'P' ? '👨 Pai' : '👶 Filho' }}
                                                    </span>
                                                @endif
                                                @if ($product->brand)
                                                    <span class="badge bg-dark">{{ $product->brand->name }}</span>
                                                @endif
                                            </div>
                                            <p class="fw-semibold text-success mb-1 text-center text-md-start fs-5">
                                                {{ currency_format($product->price) }}
                                            </p>
                                            <p
                                                class="small {{ $product->stock > 0 ? 'text-primary' : 'text-danger' }} mb-2 text-center text-md-start">
                                                <i
                                                    class="fa {{ $product->stock > 0 ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
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
                                                    <i
                                                        class="fa {{ $product->status ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                                    {{ $product->status ? 'Ativo' : 'Inativo' }}
                                                </button>
                                            </form>

                                            <a href="{{ route('admin.products.edit', $product->id) }}"
                                                class="btn btn-sm btn-warning flex-grow-1">
                                                <i class="fa fa-edit me-1"></i> Editar
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
                                                <button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                @php
                                                    $highlights = [
                                                        'destaque' => 'Destaques',
                                                        'lancamentos' => 'Lançamentos',
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
    </div>
@endsection

@section('styles')
    <style>
        .autocomplete-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 0.375rem 0.375rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .autocomplete-list.show {
            display: block;
        }

        .autocomplete-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s;
        }

        .autocomplete-item:hover,
        .autocomplete-item.active {
            background-color: #f8f9fa;
        }

        .autocomplete-item:last-child {
            border-bottom: none;
        }

        .autocomplete-item small {
            color: #6c757d;
            display: block;
            margin-top: 0.25rem;
        }

        .autocomplete-no-results {
            padding: 1rem;
            text-align: center;
            color: #6c757d;
        }

        .hover-shadow {
            transition: all 0.3s ease;
        }

        .hover-shadow:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .transition {
            transition: all 0.3s ease;
        }

        .autocomplete-input.loading {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24'%3E%3Cpath fill='%23999' d='M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z' opacity='.25'/%3E%3Cpath fill='%23333' d='M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z'%3E%3CanimateTransform attributeName='transform' type='rotate' dur='0.75s' values='0 12 12;360 12 12' repeatCount='indefinite'/%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 20px 20px;
        }

        .form-label {
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .autocomplete-list {
                max-height: 200px;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        // Dados para autocomplete
        const brandsData = @json($brands->map(fn($b) => ['id' => $b->id, 'name' => $b->name]));
        const categoriesData = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]));

        // Função genérica de autocomplete
        function initAutocomplete(inputId, listId, hiddenId, data) {
            const input = document.getElementById(inputId);
            const list = document.getElementById(listId);
            const hidden = document.getElementById(hiddenId);
            let currentFocus = -1;

            if (!input || !list || !hidden) return;

            // Evento de input
            input.addEventListener('input', function() {
                const val = this.value.trim();
                currentFocus = -1;

                if (!val) {
                    list.classList.remove('show');
                    list.innerHTML = '';
                    hidden.value = '';
                    return;
                }

                // Filtrar dados
                const filtered = data.filter(item =>
                    item.name.toLowerCase().includes(val.toLowerCase())
                );

                if (filtered.length === 0) {
                    list.innerHTML = '<div class="autocomplete-no-results">Nenhum resultado encontrado</div>';
                    list.classList.add('show');
                    return;
                }

                // Criar lista
                let html = '';
                filtered.forEach((item, index) => {
                    const highlightedName = item.name.replace(
                        new RegExp(val, 'gi'),
                        match => `<strong>${match}</strong>`
                    );
                    html += `
                    <div class="autocomplete-item" data-id="${item.id}" data-name="${item.name}" data-index="${index}">
                        <div>${highlightedName}</div>
                    </div>
                `;
                });

                list.innerHTML = html;
                list.classList.add('show');

                // Adicionar eventos de clique
                list.querySelectorAll('.autocomplete-item').forEach(item => {
                    item.addEventListener('click', function() {
                        input.value = this.dataset.name;
                        hidden.value = this.dataset.id;
                        list.classList.remove('show');
                        list.innerHTML = '';
                    });
                });
            });

            // Navegação por teclado
            input.addEventListener('keydown', function(e) {
                const items = list.querySelectorAll('.autocomplete-item');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentFocus++;
                    if (currentFocus >= items.length) currentFocus = 0;
                    setActive(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentFocus--;
                    if (currentFocus < 0) currentFocus = items.length - 1;
                    setActive(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (currentFocus > -1 && items[currentFocus]) {
                        items[currentFocus].click();
                    }
                } else if (e.key === 'Escape') {
                    list.classList.remove('show');
                    list.innerHTML = '';
                }
            });

            function setActive(items) {
                items.forEach((item, index) => {
                    item.classList.toggle('active', index === currentFocus);
                });
                if (items[currentFocus]) {
                    items[currentFocus].scrollIntoView({
                        block: 'nearest'
                    });
                }
            }

            // Fechar ao clicar fora
            document.addEventListener('click', function(e) {
                if (e.target !== input && !list.contains(e.target)) {
                    list.classList.remove('show');
                    list.innerHTML = '';
                }
            });
        }

        // Inicializar autocompletar
        document.addEventListener('DOMContentLoaded', function() {
            initAutocomplete('brandInput', 'brandList', 'brandId', brandsData);
            initAutocomplete('categoryInput', 'categoryList', 'categoryId', categoriesData);

            // Auto-submit ao mudar select
            const selects = document.querySelectorAll(
                'select[name="status_filter"], select[name="sort_by"], select[name="highlight_filter"]');
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });

            // Limpar filtros
            document.getElementById('clearFilters')?.addEventListener('click', function() {
                window.location.href = '{{ route('admin.products.index') }}';
            });
        });

        // Formulário de destaques
        document.querySelectorAll('.form-highlights').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const button = form.querySelector('button[type="submit"]');
                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Salvando...';

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    if (response.ok) {
                        const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                        modal.hide();

                        // Mostrar mensagem de sucesso
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.innerHTML = `
                        <i class="fa fa-check-circle me-2"></i>Destaques atualizados com sucesso!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                        document.querySelector('.container').insertBefore(alertDiv, document
                            .querySelector('.card'));

                        setTimeout(() => alertDiv.remove(), 3000);
                    } else {
                        throw new Error('Erro ao salvar');
                    }
                } catch (error) {
                    alert('Erro ao atualizar destaques. Tente novamente.');
                } finally {
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            });
        });
    </script>
@endsection
