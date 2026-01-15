<footer class="sax-footer-minimal">
    <div class="container">
        <div class="row g-4 justify-content-between">
            
            {{-- Coluna 1: Atendimento --}}
            <div class="col-12 col-md-3">
                <h6 class="footer-sax-title">Atención al Cliente</h6>
                <ul class="footer-sax-list">
                    <li><a href="{{ route('contact.form') }}">Ayuda y contactos</a></li>
                    <li><a href="#">Preguntas Frecuentes</a></li>
                    <li><a href="#">Pedidos y envíos</a></li>
                    <li><a href="#">Límites de importación</a></li>
                    <li><a href="#">Devoluciones y reembolsos</a></li>
                    <li><a href="#">Pagos y precios</a></li>
                    <li><a href="#">Compromiso de S.A.X. con el Cliente</a></li>
                </ul>
            </div>

            {{-- Coluna 2: Institucional --}}
            <div class="col-12 col-md-3">
                <h6 class="footer-sax-title">Acerca de S.A.X.</h6>
                <ul class="footer-sax-list">
                    <li><a href="#">Sobre nosotros</a></li>
                    <li><a href="#">Parceros de S.A.X.</a></li>
                    <li><a href="{{ route('contact.form') }}">Trabaja con nosotros</a></li>
                    <li><a href="#">Publicidad de S.A.X.</a></li>
                </ul>
            </div>

            {{-- Coluna 3: Fidelidade e Social --}}
            <div class="col-12 col-md-3">
                <h6 class="footer-sax-title">Descuentos y fidelidad</h6>
                <ul class="footer-sax-list mb-4">
                    <li><a href="#">Programa de afiliados</a></li>
                    <li><a href="#">Enviar a un amigo</a></li>
                    <li><a href="#">Programa de Lealtad</a></li>
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