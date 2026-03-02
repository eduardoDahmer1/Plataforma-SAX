<footer class="sax-footer-refined">
    <div class="container">
        @php
            $menuSlugs = ['feminino', 'masculino', 'infantil', 'optico', 'casa'];
            $footerCategories = \App\Models\Category::whereIn('slug', $menuSlugs)
                ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
                ->get();

            $labelMap = [
                'feminino' => 'MUJER',
                'masculino' => 'HOMBRE',
                'infantil' => 'NIÑOS',
                'optico' => 'LENTES',
                'casa' => 'HOGAR',
            ];
        @endphp

        <div class="footer-grid">
            {{-- Coluna 1: Categorias --}}
            <div class="footer-column">
                <h6 class="footer-title">Categorías</h6>
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
                    <li><a href="{{ route('categories.index') }}">TODAS LAS CATEGORÍAS</a></li>
                </ul>
            </div>

            {{-- Coluna 2: Institucional --}}
            <div class="footer-column border-left">
                <div class="column-content">
                    <h6 class="footer-title">Sobre S.A.X..</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('all-categories.index') }}">Categorías Gerais</a></li>
                        <li><a href="{{ route('brands.index') }}">Nuestras Marcas</a></li>
                        <li><a href="{{ route('blogs.index') }}">#SAXNEWS</a></li>
                        <li><a href="{{ route('palace.index') }}">SAX Palace</a></li>
                        <li><a href="{{ route('contact.form') }}">Trabaja con nosotros</a></li>
                    </ul>
                </div>
            </div>

            {{-- Coluna 3: Atendimento e Redes --}}
            <div class="footer-column border-left">
                <div class="column-content">
                    <h6 class="footer-title">Atención al Cliente</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('contact.form') }}">Ayuda y contacto</a></li>
                    </ul>

                    <h6 class="footer-title social-title">Síguenos en las redes</h6>
                    <div class="footer-social-icons">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-copyright">
            <p>{{ date('Y') }}. Todos los derechos reservados. SAX E-commerce.</p>
        </div>
    </div>
</footer>
<style>
    .sax-footer-refined {
        background-color: #ffffff;
        border-top: 1px solid #e0e0e0;
        padding: 100px 0 60px 0;
        font-family: 'Inter', sans-serif;
    }

    /* O segredo está aqui: limitamos a largura máxima do grid e centralizamos com auto */
    .footer-grid {
        display: flex;
        justify-content: center; /* Centraliza as colunas no meio */
        max-width: 1100px;      /* Ajuste este valor para fechar ou abrir mais o conteúdo */
        margin: 0 auto;         /* Centraliza o container do grid */
        align-items: stretch;
    }

    .footer-column {
        flex: 1;
        padding: 0 40px;        /* Espaçamento interno entre colunas */
        box-sizing: border-box;
    }

    /* Removemos o padding exagerado anterior para o conteúdo colar na linha */
    .column-content {
        padding-left: 20px; 
    }

    .footer-column:first-child {
        padding-left: 0;
    }

    .border-left {
        border-left: 1px solid #e0e0e0;
    }

    .footer-title {
        font-size: 15px;
        font-weight: 500;
        color: #1a1a1a;
        margin-bottom: 25px;
        letter-spacing: 0.2px;
        text-transform: none;
        white-space: nowrap; /* Evita que o título quebre linha */
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: #555;
        text-decoration: none;
        font-size: 13.5px;
        transition: color 0.2s ease;
        white-space: nowrap; /* Mantém os links em uma linha só como na foto */
    }

    .footer-links a:hover {
        color: #000;
    }

    .footer-social-icons {
        display: flex;
        gap: 15px;
        margin-top: 15px;
    }

    .footer-social-icons a {
        color: #333;
        font-size: 18px;
        text-decoration: none;
    }

    .footer-copyright {
        margin-top: 80px;
        padding-top: 30px;
        text-align: center;
        border-top: 1px solid #f5f5f5;
    }

    .footer-copyright p {
        font-size: 13px;
        color: #999;
    }

    /* Responsividade */
    @media (max-width: 991px) {
        .footer-grid {
            flex-direction: column;
            padding: 0 20px;
        }
        .footer-column {
            padding: 30px 0;
            border-left: none !important;
            border-bottom: 1px solid #eee;
        }
        .column-content {
            padding-left: 0;
        }
    }
</style>