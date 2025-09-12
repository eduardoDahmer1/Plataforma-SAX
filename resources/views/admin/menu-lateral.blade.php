<div class="col-md-3">
    <!-- Sidebar -->
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="list-group list-group-flush">

            <!-- Dashboard -->
            <a href="{{ route('admin.index') }}" 
               class="list-group-item list-group-item-action d-flex align-items-center">
                <i class="fa-solid fa-gauge-high me-2 text-primary"></i> 
                <span>Banners</span>
            </a>

            <!-- Catálogos -->
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
               data-bs-toggle="collapse" href="#menuCatalogos">
                <span><i class="fa-solid fa-boxes-stacked me-2 text-success"></i> Catálogos</span>
                <i class="fa-solid fa-chevron-down small"></i>
            </a>
            <div class="collapse" id="menuCatalogos">
                <a href="{{ route('admin.products.index') }}" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-box me-2"></i> Produtos
                </a>
                <a href="{{ route('admin.brands.index') }}" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-copyright me-2"></i> Marcas
                </a>
                <a href="{{ route('admin.categories.index') }}" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-tags me-2"></i> Categorias
                </a>
                <a href="{{ route('admin.subcategories.index') }}" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-tag me-2"></i> Subcategorias
                </a>
                <a href="{{ route('admin.childcategories.index') }}" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-tag me-2"></i> Categorias Filhas
                </a>
            </div>

            <!-- Vendas -->
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
               data-bs-toggle="collapse" href="#menuVendas">
                <span><i class="fa-solid fa-cart-shopping me-2 text-warning"></i> Vendas</span>
                <i class="fa-solid fa-chevron-down small"></i>
            </a>
            <div class="collapse" id="menuVendas">
                <a href="{{ route('admin.orders.index') }}" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-receipt me-2"></i> Pedidos
                </a>
                <a href="{{ route('admin.clients.index') }}" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-user-tie me-2"></i> Clientes
                </a>
                <a href="{{ route('admin.payments.index') }}" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-credit-card me-2"></i> Métodos de Pagamento
                </a>
            </div>

            <!-- Conteúdos -->
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
               data-bs-toggle="collapse" href="#menuConteudos">
                <span><i class="fa-solid fa-file-alt me-2 text-info"></i> Conteúdos</span>
                <i class="fa-solid fa-chevron-down small"></i>
            </a>
            <div class="collapse" id="menuConteudos">
                <a class="list-group-item list-group-item-action ps-5" href="{{ route('admin.blogs.index') }}">
                    <i class="fa-solid fa-blog me-2"></i> Blog
                </a>
                <a class="list-group-item list-group-item-action ps-5" href="{{ route('admin.contatos.index') }}">
                    <i class="fa-solid fa-envelope me-2"></i> Contato
                </a>
                <a class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-circle-xmark me-2"></i> Página Não Encontrada
                </a>
                <a class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-scale-balanced me-2"></i> Políticas
                </a>
            </div>

            <!-- Sistema -->
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
               data-bs-toggle="collapse" href="#menuSistema">
                <span><i class="fa-solid fa-gears me-2 text-danger"></i> Sistema</span>
                <i class="fa-solid fa-chevron-down small"></i>
            </a>
            <div class="collapse" id="menuSistema">
                <button id="clearCacheBtn" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-broom me-2"></i> Limpar Cache
                </button>
                <a href="{{ route('admin.currencies.index') }}" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-coins me-2"></i> Moedas
                </a>
                <a class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-ticket me-2"></i> Cupons
                </a>
                <a href="{{ route('admin.maintenance.index') }}" class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-screwdriver-wrench me-2"></i> Manutenção
                </a>
                <a class="list-group-item list-group-item-action ps-5">
                    <i class="fa-solid fa-envelope me-2"></i> Email
                </a>
            </div>

        </div>
    </div>
</div>
