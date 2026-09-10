@php($vistaFooterItems = app(\App\Services\OpticalNavigationService::class)->items()->take(7))

<footer class="vista-footer">
    <div class="vista-footer__grid vista-footer__grid--desktop">
        <div>
            <h2>Catálogo óptico</h2>
            <nav class="vista-footer__catalog" aria-label="Catálogo óptico">
                @foreach($vistaFooterItems as $item)
                    <a class="vista-footer__{{ $item['type'] }}" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>
        </div>
        <div>
            <h2>Acerca de VISTA&amp;CO</h2>
            <a href="{{ route('categories.index') }}">Categorías ópticas</a>
            <a href="{{ route('brands.index') }}">Nuestras marcas</a>
            <a href="{{ route('contact.form') }}">Trabaja con nosotros</a>
        </div>
        <div>
            <h2>Descuentos y fidelidad</h2>
            <a href="{{ route('contact.form') }}">Ayuda y contacto</a>
            <a href="{{ route('policies.index') }}">Política y términos</a>
            <h2 class="vista-footer__social-title">Síguenos en las redes</h2>
            <div class="vista-footer__socials">
                <a href="https://www.instagram.com/saxdepartment/" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.facebook.com/saxdepartmentstore" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://www.tiktok.com/@saxdepartment" target="_blank" rel="noopener" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                <a href="https://www.youtube.com/" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>
    </div>
    <div class="vista-footer__mobile">
        <details>
            <summary>Catálogo óptico <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
            <nav class="vista-footer__catalog" aria-label="Catálogo óptico">
                @foreach($vistaFooterItems as $item)
                    <a class="vista-footer__{{ $item['type'] }}" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>
        </details>
        <details>
            <summary>Acerca de VISTA&amp;CO <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
            <a href="{{ route('categories.index') }}">Categorías ópticas</a>
            <a href="{{ route('brands.index') }}">Nuestras marcas</a>
            <a href="{{ route('contact.form') }}">Trabaja con nosotros</a>
        </details>
        <details>
            <summary>Ayuda <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></summary>
            <a href="{{ route('contact.form') }}">Ayuda y contacto</a>
            <a href="{{ route('policies.index') }}">Política y términos</a>
        </details>
        <div class="vista-footer__mobile-socials">
            <strong>Síguenos</strong>
            <div class="vista-footer__socials">
                <a href="https://www.instagram.com/saxdepartment/" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.facebook.com/saxdepartmentstore" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://www.tiktok.com/@saxdepartment" target="_blank" rel="noopener" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                <a href="https://www.youtube.com/" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>
    </div>
    <div class="vista-footer__bottom">© {{ date('Y') }} VISTA&amp;CO</div>
</footer>
