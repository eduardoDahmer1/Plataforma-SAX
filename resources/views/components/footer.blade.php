<footer class="bg-dark text-white pt-5 pb-3">
    <div class="container">

        {{-- Logo --}}
        @if ($webpImage)
            <div class="text-center mb-4">
                <a href="{{ route('home') }}" class="d-inline-block">
                    <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Logo Footer" class="img-fluid"
                         style="max-height:100px; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                </a>
            </div>
        @endif

        {{-- Links principais --}}
        <div class="row text-center text-md-start mb-4">
            <div class="col-12 col-md-6 col-lg-4 mb-3 mb-md-0">
                <h5 class="fw-bold text-uppercase mb-3">Navegação</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('home') }}" class="text-white text-decoration-none hover-link">
                            <i class="fa fa-home me-2"></i> Home
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('blogs.index') }}" class="text-white text-decoration-none hover-link">
                            <i class="fa fa-blog me-2"></i> Blog
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('brands.index') }}" class="text-white text-decoration-none hover-link">
                            <i class="fa fa-tag me-2"></i> Marcas
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('categories.index') }}" class="text-white text-decoration-none hover-link">
                            <i class="fa fa-list me-2"></i> Categorias
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-12 col-md-6 col-lg-4 mb-3 mb-md-0">
                <h5 class="fw-bold text-uppercase mb-3">Categorias</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('subcategories.index') }}" class="text-white text-decoration-none hover-link">
                            <i class="fa fa-layer-group me-2"></i> Subcategorias
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('childcategories.index') }}" class="text-white text-decoration-none hover-link">
                            <i class="fa fa-sitemap me-2"></i> Categorias Filhas
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('contact.form') }}" class="text-white text-decoration-none hover-link">
                            <i class="fa fa-envelope me-2"></i> Contato
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-12 col-lg-4">
                <h5 class="fw-bold text-uppercase mb-3">Precisa de ajuda?</h5>
                <p class="mb-3 fs-6">
                    Entre em contato conosco pelo WhatsApp para suporte ou dúvidas:
                </p>
                <a href="https://wa.me/{{ env('WHATSAPP_NUMBER') }}?text={{ urlencode('Olá, você visitou nosso site. Precisa de ajuda com algo?') }}"
                   target="_blank"
                   class="btn btn-success w-100 w-md-auto px-4 shadow-sm mb-2">
                    <i class="fab fa-whatsapp me-2"></i> Fale Conosco
                </a>

                {{-- Métodos de pagamento --}}
                <div class="mt-4">
                    <h6 class="fw-bold mb-2">Métodos de Pagamento</h6>
                    <p class="mb-1">
                        <i class="fa fa-university me-2"></i> Depósito Bancário
                    </p>
                    <p class="mb-0">
                        <i class="fa fa-comments me-2"></i> Checkout via WhatsApp
                    </p>
                </div>
            </div>
        </div>

        <hr class="border-light">

        {{-- Direitos autorais --}}
        <div class="text-center fs-6 mt-3">
            <small>&copy; {{ date('Y') }} Todos os direitos reservados. <span class="fw-bold">Sax E-commerce</span></small>
        </div>

    </div>

    {{-- CSS Inline --}}
    <style>
        footer {
            font-size: 0.95rem;
        }

        .hover-link:hover {
            color: #0d6efd !important;
            text-decoration: underline;
            transition: all 0.3s;
        }

        @media (max-width: 768px) {
            footer .row > div {
                text-align: center;
            }
            footer .btn {
                width: 100% !important;
            }
        }
    </style>
</footer>
