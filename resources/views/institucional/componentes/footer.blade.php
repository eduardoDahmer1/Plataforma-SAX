<footer class="footer-inst">
    <div class="container">
        <div class="row g-5 justify-content-between">

            {{-- Coluna 1: Logo Institucional + Descrição --}}
            <div class="col-lg-4 col-md-12">
                <a href="/" class="footer-brand-inst">
                    SAX <span>INSTITUCIONAL</span>
                </a>
    
                <p class="footer-desc-inst">
                    {{ $institucional->section_one_content ? Str::limit($institucional->section_one_content, 150) : 'Líder no mercado de luxo, a SAX Department oferece uma experiência única em Ciudad del Este, reunindo as marcas mais prestigiosas do mundo.' }}
                </p>

                <div class="footer-social-inst">
                    <a href="https://www.instagram.com/saxdepartment" target="_blank" class="footer-social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.facebook.com/saxdepartment" target="_blank" class="footer-social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://wa.me/595983123456" target="_blank" class="footer-social-link" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            {{-- Coluna 2: Estatísticas Rápidas --}}
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-col-title">Números</h6>
                <div class="footer-stats-list">
                    <div class="stat-item">
                        <span class="stat-label">Marcas Parceiras</span>
                        <span class="stat-value">{{ $institucional->stat_brands_count ?? '200' }}+</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Área de Loja</span>
                        <span class="stat-value">{{ $institucional->stat_sqm_count ?? '17' }}k m²</span>
                    </div>
                    <div class="stat-item last">
                        <span class="stat-label">Equipe SAX</span>
                        <span class="stat-value">{{ $institucional->stat_employees_count ?? '500' }}+</span>
                    </div>
                </div>
            </div>

            {{-- Coluna 3: Navegação --}}
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-col-title">Explorar</h6>
                <ul class="footer-links-list">
                    <li><a href="/" class="footer-link">Home Institucional</a></li>
                    <li><a href="{{ route('blogs.index') }}" class="footer-link">#SAXNEWS</a></li>
                    <li><a href="{{ route('bridal.index') }}" class="footer-link">SAX Bridal</a></li>
                    <li><a href="{{ route('contact.form') }}" class="footer-link">Fale Conosco</a></li>
                    <li><a href="#" class="footer-link">Trabalhe Conosco</a></li>
                </ul>
            </div>

        </div>

        {{-- Bottom bar --}}
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="copyright-text">&copy; {{ date('Y') }} SAX Department. Todos os direitos reservados.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                    <span class="location-text"><i class="bi bi-geo-alt me-2"></i> Ciudad del Este, Paraguai</span>
                </div>
            </div>
        </div>
    </div>
</footer>
