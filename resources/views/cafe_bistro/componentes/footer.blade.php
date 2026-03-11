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
                    @if($cafeBistro->instagram_url)
                        <a href="{{ $cafeBistro->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram" class="footer-social-link">
                            <i class="bi bi-instagram"></i>
                        </a>
                    @endif
                    @if($cafeBistro->facebook_url)
                        <a href="{{ $cafeBistro->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook" class="footer-social-link">
                            <i class="bi bi-facebook"></i>
                        </a>
                    @endif
                    <a href="{{ $cafeBistro->whatsapp_link }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="footer-social-link">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            {{-- Col 2: Horários resumidos --}}
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-col-title">Horários</h6>

                <table class="footer-horarios w-100">
                    <tbody>
                        @foreach($cafeBistro->horarios ?? [] as $h)
                            <tr>
                                <td class="footer-dia">{{ $h['dia'] }}</td>
                                <td class="footer-hora {{ !$h['apertura'] ? 'footer-fechado' : '' }}">
                                    {{ $h['apertura'] ? $h['apertura'] . ' — ' . $h['cierre'] : 'Fechado' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Col 3: Endereço + Telefone + Reserva --}}
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-col-title">Localização & Contacto</h6>

                <address class="footer-endereco">
                    <i class="bi bi-geo-alt me-2"></i>
                    {{ $cafeBistro->direccion ?? 'Shopping Dubai, Pedro Juan Caballero — Paraguai' }}
                </address>

                <p class="footer-telefone">
                    <i class="bi bi-telephone me-2"></i>
                    {{ $cafeBistro->telefono ?? '+595 000 000 000' }}
                </p>

                <a href="{{ $cafeBistro->whatsapp_link }}" target="_blank" rel="noopener" class="btn-reservar-cafe d-inline-block mt-2">
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
