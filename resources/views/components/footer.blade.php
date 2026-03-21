<footer class="sax-footer-refined">
    <div class="container">
        @php
            $menuSlugs = ['feminino', 'masculino', 'infantil', 'optico', 'casa'];
            $footerCategories = \App\Models\Category::whereIn('slug', $menuSlugs)
                ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
                ->get();

            $labelMap = [
                'feminino' => 'FEMININO',
                'masculino' => 'MASCULINO',
                'infantil' => 'INFANTIL',
                'optico' => 'ÓPTICA',
                'casa' => 'CASA',
            ];
        @endphp

        <div class="footer-grid">
            {{-- Coluna 1: Categorias --}}
            <div class="footer-column">
                <h6 class="footer-title">Categorias</h6>
                <ul class="footer-links">
                    @foreach ($footerCategories as $cat)
                        <li>
                            <a href="{{ url('categorias/' . $cat->slug) }}">
                                {{ $labelMap[$cat->slug] ?? strtoupper($cat->name) }}
                            </a>
                        </li>
                    @endforeach
                    <li><a href="{{ route('bridal.index') }}">BRIDAL</a></li>
                    <li><a href="{{ route('palace.index') }}">PALACE</a></li>
                    <li><a href="{{ route('categories.index') }}">TODAS AS CATEGORIAS</a></li>
                </ul>
            </div>

            {{-- Coluna 2: Institucional --}}
            <div class="footer-column border-left">
                <div class="column-content">
                    <h6 class="footer-title">Sobre a S.A.X.</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('all-categories.index') }}">Categorias Gerais</a></li>
                        <li><a href="{{ route('brands.index') }}">Nossas Marcas</a></li>
                        <li><a href="{{ route('blogs.index') }}">#SAXNEWS</a></li>
                        <li><a href="{{ route('palace.index') }}">SAX Palace</a></li>
                        <li><a href="{{ route('contact.form') }}">Trabalhe conosco</a></li>
                    </ul>
                </div>
            </div>

            {{-- Coluna 3: Atendimento e Redes --}}
            <div class="footer-column border-left">
                <div class="column-content">
                    <h6 class="footer-title">Atendimento ao Cliente</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('contact.form') }}">Ajuda e contato</a></li>
                    </ul>

                    <h6 class="footer-title social-title">Siga-nos nas redes</h6>
                    <div class="footer-social-icons">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-copyright">
            <p>{{ date('Y') }}. Todos os direitos reservados. SAX E-commerce.</p>
        </div>
    </div>
</footer>
