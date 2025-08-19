<footer class="bg-dark text-white py-5">
    <div class="container">

        {{-- Logo --}}
        @if ($webpImage)
        <div class="text-center mb-4">
            <a href="{{ route('home') }}">
                <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Logo Footer" 
                     class="img-fluid" style="max-height:90px;">
            </a>
        </div>
        @endif

        {{-- Links principais --}}
        <div class="d-flex flex-wrap justify-content-center gap-4 mb-4 fs-5">
            <a href="{{ route('home') }}" class="text-white text-decoration-none">
                <i class="fa fa-home me-2"></i> Home
            </a>
            <a href="{{ route('blogs.index') }}" class="text-white text-decoration-none">
                <i class="fa fa-blog me-2"></i> Blog
            </a>
            <a href="{{ route('brands.index') }}" class="text-white text-decoration-none">
                <i class="fa fa-tag me-2"></i> Marcas
            </a>
            <a href="{{ route('categories.index') }}" class="text-white text-decoration-none">
                <i class="fa fa-list me-2"></i> Categorias
            </a>
            <a href="{{ route('subcategories.index') }}" class="text-white text-decoration-none">
                <i class="fa fa-layer-group me-2"></i> Subcategorias
            </a>
            <a href="{{ route('childcategories.index') }}" class="text-white text-decoration-none">
                <i class="fa fa-sitemap me-2"></i> Categorias Filhas
            </a>
            <a href="{{ route('contact.form') }}" class="text-white text-decoration-none">
                <i class="fa fa-envelope me-2"></i> Contato
            </a>
        </div>

        {{-- Métodos de pagamento --}}
        <div class="text-center mb-4 fs-6">
            <p class="mb-1"><strong>Métodos de Pagamento:</strong></p>
            <p class="mb-0">
                <i class="fa fa-credit-card me-2"></i> Bancard |
                <i class="fa fa-university me-2"></i> Depósito Bancário |
                <i class="fa fa-comments me-2"></i> Checkout Personalizado via WhatsApp
            </p>
        </div>

        {{-- Direitos autorais --}}
        <div class="text-center fs-6">
            <small>&copy; {{ date('Y') }} Todos os direitos reservados. Sax E-commerce</small>
        </div>

    </div>
</footer>

{{-- CSS para fixar no final da página --}}
<style>
html, body {
    height: 100%;
}

body {
    display: flex;
    flex-direction: column;
}

footer {
    flex-shrink: 0;
    font-size: 1rem;
    line-height: 1.6;
}
</style>
