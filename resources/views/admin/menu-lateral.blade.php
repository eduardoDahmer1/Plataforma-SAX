<div class="col-md-3">
    <!-- Sidebar -->
    <div class="list-group shadow-sm rounded">
        <a href="{{ route('admin.index') }}" class="list-group-item list-group-item-action">
            <i class="fa-solid fa-gauge-high me-2"></i> Admin
        </a>
        <a href="{{ route('home') }}" class="list-group-item list-group-item-action">
            <i class="fa-solid fa-house me-2"></i> Home
        </a>
        <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action">
            <i class="fa-solid fa-users me-2"></i> Usuários
        </a>

        <!-- Catálogos -->
        <a class="list-group-item list-group-item-action dropdown-toggle" data-bs-toggle="collapse"
            href="#menuCatalogos" role="button" aria-expanded="false" aria-controls="menuCatalogos">
            <i class="fa-solid fa-boxes-stacked me-2"></i> Catálogos
        </a>
        <div class="collapse" id="menuCatalogos">
            <a href="{{ route('admin.products.index') }}" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-box me-2"></i> Produtos
            </a>
            <a href="{{ route('admin.brands.index') }}" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-copyright me-2"></i> Marcas
            </a>
            <a href="{{ route('admin.categories.index') }}" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-tags me-2"></i> Categorias
            </a>
            <a href="{{ route('admin.subcategories.index') }}" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-tag me-2"></i> Subcategorias
            </a>
            <a href="{{ route('admin.childcategories.index') }}" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-tag me-2"></i> Categorias Filhas
            </a>
        </div>

        <!-- Vendas -->
        <a class="list-group-item list-group-item-action dropdown-toggle" data-bs-toggle="collapse" href="#menuVendas"
            role="button" aria-expanded="false" aria-controls="menuVendas">
            <i class="fa-solid fa-cart-shopping me-2"></i> Vendas
        </a>
        <div class="collapse" id="menuVendas">
            <a href="{{ route('admin.orders.index') }}" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-receipt me-2"></i> Pedidos
            </a>
            <a href="{{ route('admin.clients.index') }}" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-user-tie me-2"></i> Clientes
            </a>
            <a href="{{ route('admin.payments.index') }}" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-credit-card me-2"></i> Métodos de Pagamento
            </a>
        </div>

        <!-- Conteúdos -->
        <a class="list-group-item list-group-item-action dropdown-toggle" data-bs-toggle="collapse"
            href="#menuConteudos" role="button" aria-expanded="false" aria-controls="menuConteudos">
            <i class="fa-solid fa-file-alt me-2"></i> Conteúdos
        </a>
        <div class="collapse" id="menuConteudos">
            <a class="list-group-item list-group-item-action ps-4" href="{{ route('admin.blogs.index') }}">
                <i class="fa-solid fa-blog me-2"></i> Blog
            </a>
            <a class="list-group-item list-group-item-action ps-4" href="{{ route('admin.contatos.index') }}">
                <i class="fa-solid fa-envelope me-2"></i> Contato
            </a>
            <a class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-circle-xmark me-2"></i> Página Não Encontrada
            </a>
            <a class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-scale-balanced me-2"></i> Políticas
            </a>
        </div>

        <!-- Sistema -->
        <a class="list-group-item list-group-item-action dropdown-toggle" data-bs-toggle="collapse" href="#menuSistema"
            role="button" aria-expanded="false" aria-controls="menuSistema">
            <i class="fa-solid fa-gears me-2"></i> Sistema
        </a>
        <div class="collapse" id="menuSistema">
            <button id="clearCacheBtn" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-broom me-2"></i> Limpar Cache
            </button>
            <a href="{{ route('admin.convert.webp') }}" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-image me-2"></i> Converter todas as imagens para WebP
            </a>
            <a href="{{ route('admin.currencies.index') }}" class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-image me-2"></i> Moedas
            </a>
            <a class="list-group-item list-group-item-action ps-4">
                <i class="fa-solid fa-ticket me-2"></i> Cupons
            </a>
            <a href="{{ route('admin.maintenance.index') }}" 
            class="list-group-item list-group-item-action ps-4">
             <i class="fa-solid fa-screwdriver-wrench me-2"></i> Manutenção
         </a>             
            <a class="list-group-item list-group-item-action ps-4">
            <i class="fa-solid fa-envelope me-2"></i> Email
            </a>
        </div>
    </div>
</div>
