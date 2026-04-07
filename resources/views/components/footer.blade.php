<footer class="sax-footer-refined">
    <div class="container">
        @php
            $menuSlugs = ['feminino', 'masculino', 'infantil', 'optico', 'casa'];
            $footerCategories = \App\Models\Category::whereIn('slug', $menuSlugs)
                ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
                ->get();

            // O Map agora busca a tradução baseada no slug
            $labelMap = [
                'feminino' => __('messages.feminino'),
                'masculino' => __('messages.masculino'),
                'infantil' => __('messages.infantil'),
                'optico' => __('messages.otica'),
                'casa' => __('messages.casa'),
            ];
        @endphp

        <div class="footer-grid">
            {{-- Coluna 1: Categorias --}}
            <div class="footer-column">
                <h6 class="footer-title">{{ __('messages.categorias') }}</h6>
                <ul class="footer-links">
                    @foreach ($footerCategories as $cat)
                        <li>
                            <a href="{{ url('categorias/' . $cat->slug) }}">
                                {{ $labelMap[$cat->slug] ?? strtoupper($cat->name) }}
                            </a>
                        </li>
                    @endforeach
                    <li><a href="{{ route('bridal.index') }}">{{ __('messages.bridal') }}</a></li>
                    <li><a href="{{ route('palace.index') }}">{{ __('messages.palace') }}</a></li>
                    <li><a href="{{ route('categories.index') }}">{{ __('messages.todas_categorias') }}</a></li>
                </ul>
            </div>

            {{-- Coluna 2: Institucional --}}
            <div class="footer-column border-left">
                <div class="column-content">
                    <h6 class="footer-title">{{ __('messages.sobre_nos') }}</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('all-categories.index') }}">{{ __('messages.categorias') }} Gerais</a></li>
                        <li><a href="{{ route('brands.index') }}">{{ __('messages.nossas_marcas') }}</a></li>
                        <li><a href="{{ route('blogs.index') }}">#SAXNEWS</a></li>
                        <li><a href="{{ route('palace.index') }}">SAX Palace</a></li>
                        <li><a href="{{ route('contact.form') }}">{{ __('messages.trabalhe_conosco') }}</a></li>
                    </ul>
                </div>
            </div>

            {{-- Coluna 3: Atendimento e Redes --}}
            <div class="footer-column border-left">
                <div class="column-content">
                    <h6 class="footer-title">{{ __('messages.atendimento') }}</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('contact.form') }}">{{ __('messages.ajuda') }}</a></li>
                    </ul>

                    <h6 class="footer-title social-title">{{ __('messages.siga_redes') }}</h6>
                    <div class="footer-social-icons">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-copyright">
            <p>{{ date('Y') }}. {{ __('messages.direitos') }}. SAX E-commerce.</p>
        </div>
    </div>
</footer>