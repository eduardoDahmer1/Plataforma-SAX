<footer class="sax-footer-minimal">
    <div class="container">
        @php
            // Reutilizando a lógica do header para manter consistência
            $menuSlugs = ['feminino', 'masculino', 'infantil', 'optico', 'casa'];
            $footerCategories = \App\Models\Category::whereIn('slug', $menuSlugs)
                ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
                ->get();

            $labelMap = [
                'feminino'  => 'MUJER',
                'masculino' => 'HOMBRE',
                'infantil'  => 'NIÑOS',
                'optico'    => 'LENTES',
                'casa'      => 'HOGAR'
            ];
        @endphp

        <div class="row g-4 justify-content-between">
            
            {{-- Coluna 1: Categorias (Extraídas do Header) --}}
            <div class="col-12 col-md-3">
                <h6 class="footer-sax-title">Categorías</h6>
                <ul class="footer-sax-list">
                    @foreach($footerCategories as $cat)
                        <li>
                            <a href="{{ url('categorias/' . $cat->slug) }}">
                                {{ $labelMap[$cat->slug] ?? strtoupper($cat->name) }}
                            </a>
                        </li>
                    @endforeach
                    <li><a href="{{ route('bridal.index') }}">BRIDAL</a></li>
                    <li><a href="{{ route('palace.index') }}">PALACE</a></li>
                    <li><a href="{{ route('categories.index') }}">TODAS LAS CATEGORÍAS</a></li>
                </ul>
            </div>

            {{-- Coluna 2: Institucional e Marcas --}}
            <div class="col-12 col-md-3">
                <h6 class="footer-sax-title">Sobre S.A.X.</h6>
                <ul class="footer-sax-list">
                    <li><a href="{{ route('brands.index') }}">Nuestras Marcas</a></li>
                    <li><a href="{{ route('blogs.index') }}">#SAXNEWS</a></li>
                    <li><a href="{{ route('palace.index') }}">Servicios SAX Palace</a></li>
                    <li><a href="{{ route('contact.form') }}">Trabaja con nosotros</a></li>
                </ul>
            </div>

            {{-- Coluna 3: Atendimento e Contato --}}
            <div class="col-12 col-md-3">
                <h6 class="footer-sax-title">Atención al Cliente</h6>
                <ul class="footer-sax-list mb-4">
                    <li><a href="{{ route('contact.form') }}">Ayuda y contacto</a></li>
                </ul>

                <h6 class="footer-sax-title mb-3">Síguenos en las redes</h6>
                <div class="footer-sax-social">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>

        {{-- Copyright --}}
        <div class="footer-sax-bottom">
            <p>{{ date('Y') }}. Todos los derechos reservados. SAX E-commerce.</p>
        </div>
    </div>
</footer>
<style>
    /* Container Principal do Rodapé */
.sax-footer-minimal {
    background-color: #e0e0e0; /* Cinza claro idêntico ao exemplo */
    color: #333;
    padding: 60px 0 30px 0;
    font-family: 'Inter', sans-serif;
}

/* Títulos das Colunas */
.footer-sax-title {
    font-size: 0.95rem;
    font-weight: 500;
    color: #000;
    margin-bottom: 20px;
    letter-spacing: 0.3px;
}

/* Listas de Links */
.footer-sax-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-sax-list li {
    margin-bottom: 12px;
}

.footer-sax-list a {
    color: #555;
    text-decoration: none;
    font-size: 0.85rem;
    transition: color 0.2s ease;
}

.footer-sax-list a:hover {
    color: #000;
    text-decoration: none;
}

/* Ícones Sociais */
.footer-sax-social {
    display: flex;
    gap: 15px;
    font-size: 1.2rem;
}

.footer-sax-social a {
    color: #333;
    transition: transform 0.2s ease;
}

.footer-sax-social a:hover {
    transform: translateY(-3px);
    color: #000;
}

/* Linha de Copyright */
.footer-sax-bottom {
    margin-top: 50px;
    padding-top: 20px;
    text-align: center;
}

.footer-sax-bottom p {
    font-size: 0.8rem;
    color: #777;
    letter-spacing: 0.5px;
}

/* Responsividade */
@media (max-width: 768px) {
    .sax-footer-minimal {
        text-align: left;
        padding: 40px 20px;
    }
    
    .footer-sax-title {
        margin-top: 20px;
    }
}
</style>