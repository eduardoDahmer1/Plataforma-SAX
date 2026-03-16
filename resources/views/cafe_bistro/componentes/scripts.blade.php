{{-- SAX Café & Bistrô — Scripts --}}
<script>
(function () {

    // ─── Navbar: scrolled + hide on scroll ────────────────────────────────
    const nav       = document.getElementById('mainHeader');
    let lastY       = window.scrollY;
    let ticking     = false;
    const THRESHOLD = 100;
    const DELTA     = 5;

    window.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;

        window.requestAnimationFrame(function () {
            const currentY = window.scrollY;
            const diff     = currentY - lastY;

            nav.classList.toggle('scrolled', currentY > 50);

            if (currentY > THRESHOLD) {
                if (diff > DELTA)       nav.classList.add('nav-hidden');
                else if (diff < -DELTA) nav.classList.remove('nav-hidden');
            } else {
                nav.classList.remove('nav-hidden');
            }

            lastY   = currentY;
            ticking = false;
        });
    });


    // ─── MenuModal (overlay customizado) ───────────────────────────────────
    const overlay   = document.getElementById('menuOverlay');
    const container = overlay ? overlay.querySelector('.menu-overlay-container') : null;

    function openMenu() {
        if (!overlay) return;
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        if (!overlay) return;
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    // Botão abrir
    document.querySelectorAll('.btn-open-menu').forEach(function (btn) {
        btn.addEventListener('click', openMenu);
    });

    // Botão fechar
    document.querySelectorAll('.btn-close-menu').forEach(function (btn) {
        btn.addEventListener('click', closeMenu);
    });

    // Click fora do container interno fecha o overlay
    if (overlay) {
        overlay.addEventListener('click', function (e) {
            if (container && !container.contains(e.target)) {
                closeMenu();
            }
        });
    }

    // ESC fecha o overlay
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });


    // ─── Reveal on scroll (IntersectionObserver) ───────────────────────────
    const revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('[data-reveal]').forEach(function (el) {
        revealObserver.observe(el);
    });


    // ─── Swiper: Carrusel de eventos ────────────────────────────────────
    if (document.querySelector('.eventosSwiper')) {
        new Swiper('.eventosSwiper', {
            loop: true,
            speed: 800,
            autoplay: { delay: 5000, disableOnInteraction: false },
            effect: 'fade',
            fadeEffect: { crossFade: true },
        });
    }


    // ─── Smooth scroll para âncoras do navbar ──────────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // fecha o menu mobile se estiver aberto
            const collapse = document.getElementById('expNavbarMobile');
            if (collapse && collapse.classList.contains('show')) {
                bootstrap.Collapse.getInstance(collapse)?.hide();
            }
        });
    });

})();
</script>
