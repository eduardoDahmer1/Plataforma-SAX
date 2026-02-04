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
                
                <p class="text-secondary pe-lg-5">
                    O SAX Palace redefine o conceito de luxo e sofisticação em Ciudad del Este, 
                    proporcionando momentos inesquecíveis em um ambiente exclusivo no 11º andar.
                </p>
                <div class="mt-4">
                    <a href="#" class="social-circle"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-circle"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-circle"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 footer-links">
                <h5 class="mb-4 font-serif">O Palace</h5>
                <ul>
                    <li><a href="#">Nossa História</a></li>
                    <li><a href="{{ route('contact.form') }}">Trabalhe Conosco</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 footer-links">
                <h5 class="mb-4 font-serif">Serviços</h5>
                <ul>
                    <li><p>Café da Manhã</p></li>
                    <li><p>Bodega de Vinhos</p></li>
                    <li><p>Casamentos (Boda)</p></li>
                    <li><p>Eventos Corporativos</p></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h5 class="mb-4 font-serif">Newsletter</h5>
                <p class="text-secondary small mb-4">Receba convites exclusivos para nossas noites temáticas.</p>
            </div>
        </div>
    </div>

    <div class="copyright text-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start">
                    © {{ date('Y') }} SAX Palace. Todos os direitos reservados.
                </div>
                <div class="col-md-6 text-md-end">
                    Desenvolvido por SAX Full Service
                </div>
            </div>
        </div>
    </div>
</footer>