{{-- Tarefa 10: Footer --}}
<footer class="footer-cafe">
    <div class="container">

        <div class="row gy-5">

            {{-- Col 1: Logo + Descrição + Redes --}}
            <div class="col-lg-4 col-md-12">
                {{-- Placeholder logo --}}
                <div class="footer-logo-placeholder mb-3">
                    SAX <span>Café & Bistrô</span>
                </div>

                <p class="footer-descricao">
                    Um espaço de sabores, cultura e encontros no coração do Shopping Dubai.
                    Venha viver a experiência SAX.
                </p>

                <div class="footer-social">
                    <a href="#" target="_blank" rel="noopener" aria-label="Instagram" class="footer-social-link">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" target="_blank" rel="noopener" aria-label="Facebook" class="footer-social-link">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://wa.me/" target="_blank" rel="noopener" aria-label="WhatsApp" class="footer-social-link">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            {{-- Col 2: Horários resumidos --}}
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-col-title">Horários</h6>

                <table class="footer-horarios w-100">
                    <tbody>
                        <tr>
                            <td class="footer-dia">Segunda</td>
                            <td class="footer-hora footer-fechado">Fechado</td>
                        </tr>
                        <tr>
                            <td class="footer-dia">Ter — Qui</td>
                            <td class="footer-hora">09:00 — 23:00</td>
                        </tr>
                        <tr>
                            <td class="footer-dia">Sex — Sáb</td>
                            <td class="footer-hora">09:00 — 23:30</td>
                        </tr>
                        <tr>
                            <td class="footer-dia">Domingo</td>
                            <td class="footer-hora">09:00 — 23:00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Col 3: Endereço + Telefone + Reserva --}}
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-col-title">Localização & Contacto</h6>

                <address class="footer-endereco">
                    <i class="bi bi-geo-alt me-2"></i>
                    Shopping Dubai, Pedro Juan Caballero — Paraguai
                </address>

                <p class="footer-telefone">
                    <i class="bi bi-telephone me-2"></i>
                    +595 000 000 000
                </p>

                <a href="https://wa.me/" target="_blank" rel="noopener" class="btn-reservar-cafe d-inline-block mt-2">
                    Fazer Reserva
                </a>
            </div>

        </div>

        {{-- Linha inferior --}}
        <div class="footer-bottom">
            <p class="footer-copyright">
                &copy; 2026 SAX Café &amp; Bistró &middot; Todos os direitos reservados
            </p>
        </div>

    </div>
</footer>
