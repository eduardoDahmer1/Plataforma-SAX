<footer class="palace-footer">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4 col-md-6">
                <div class="footer-logo">
                    @if(isset($attributes) && $attributes->logo_palace)
                        <img src="{{ asset('storage/uploads/' . $attributes->logo_palace) }}" alt="SAX Logo">
                    @else
                        <img src="{{ asset('images/logo-sax-white.png') }}" alt="SAX Logo">
                    @endif
                </div>
                
                {{-- Descrição dinâmica vinda do Hero ou você pode usar um campo fixo --}}
                <p class="text-secondary pe-lg-5">
                    {{ Str::limit($palace->hero_descricao, 150) }}
                </p>
                <div class="mt-4">
                    <a href="#" class="social-circle"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/saxpalace" target="_blank" class="social-circle"><i class="fab fa-instagram"></i></a>
                    {{-- WhatsApp dinâmico vindo do banco --}}
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" target="_blank" class="social-circle">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 footer-links">
                <h5 class="mb-4 font-serif">Localização</h5>
                <ul class="list-unstyled text-secondary">
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt text-gold me-2"></i> 
                        {{ $palace->contato_endereco }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock text-gold me-2"></i> 
                        <strong>Seg:</strong> {{ $palace->contato_horario_segunda }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock text-gold me-2"></i> 
                        <strong>Ter-Sáb:</strong> {{ $palace->contato_horario_sabado }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-clock text-gold me-2"></i> 
                        <strong>Dom:</strong> {{ $palace->contato_horario_domingo }}
                    </li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 footer-links">
                <h5 class="mb-4 font-serif">Links Úteis</h5>
                <ul>
                    <li><a href="/">Início</a></li>
                    <li><a href="{{ route('contact.form') }}">Contato</a></li>
                    <li><a href="#reservas">Reservas</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5 class="mb-4 font-serif">Newsletter</h5>
                <p class="text-secondary small mb-4">Receba convites exclusivos para o <strong>{{ $palace->tematica_titulo }}</strong> e outros eventos.</p>
                <form action="#" class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control bg-transparent border-secondary text-white" placeholder="Seu e-mail">
                        <button class="btn btn-gold" type="button"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="copyright text-center mt-5">
        <div class="container border-top border-secondary pt-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start small">
                    © {{ date('Y') }} SAX Palace. Todos os direitos reservados.
                </div>
                <div class="col-md-6 text-md-end small">
                    Desenvolvido por <strong>SAX Full Service</strong>
                </div>
            </div>
        </div>
    </div>
</footer>