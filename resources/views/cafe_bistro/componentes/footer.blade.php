{{-- Tarefa 10: Footer --}}
<footer class="footer-cafe">
    <div class="container">

        <div class="row gy-5">

            {{-- Col 1: Logo + Descrição + Redes --}}
            <div class="col-lg-4 col-md-12">
                {{-- Logo --}}
                <div class="mb-3">
                    @if(!empty($attributes->logo_cafe_bistro))
                        <img src="{{ asset('storage/uploads/' . $attributes->logo_cafe_bistro) }}"
                             alt="SAX Café & Bistrô" style="height:3.5rem;width:auto;">
                    @else
                        <div class="footer-logo-placeholder">
                            SAX <span>Café & Bistrô</span>
                        </div>
                    @endif
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

                @php $horarios = $cafeBistro->horarios ?? []; @endphp
                <table class="footer-horarios w-100">
                    <tbody>
                        <tr>
                            <td class="footer-dia">Segunda-feira</td>
                            <td class="footer-hora">{{ $horarios['segunda'] ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="footer-dia">Terça-feira — Quinta-feira</td>
                            <td class="footer-hora">{{ $horarios['terca_quinta'] ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="footer-dia">Sexta-feira — Sábado</td>
                            <td class="footer-hora">{{ $horarios['sexta_sabado'] ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="footer-dia">Domingo</td>
                            <td class="footer-hora">{{ $horarios['domingo'] ?? '—' }}</td>
                        </tr>
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
                    {{ $cafeBistro->telefono ?? '+595 993 011502' }}
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
