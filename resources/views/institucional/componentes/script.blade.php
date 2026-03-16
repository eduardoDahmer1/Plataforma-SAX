<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- INICIALIZAÇÃO DE BIBLIOTECAS ---
        
        // AOS (Animações de Scroll)
        AOS.init({
            duration: 1000,
            once: true
        });

        // Fancybox (Modal de Imagens da Galeria)
        Fancybox.bind("[data-fancybox]", {
            activeClassName: "fancybox-active",
            dragToClose: true,
            Toolbar: {
                display: {
                    left: ["infobar"],
                    middle: [],
                    right: ["iterateZoom", "slideshow", "fullScreen", "download", "thumbs", "close"],
                },
            },
            Images: {
                initialSize: "fit",
            },
        });

        // Swiper: Slider Principal (Hero)
        new Swiper(".mainSwiper", {
            loop: true,
            effect: "fade",
            fadeEffect: { crossFade: true },
            speed: 1500,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });

        // Swiper: Carrossel de Marcas
        new Swiper(".brandsSwiper", {
            slidesPerView: 2,
            spaceBetween: 40,
            loop: true,
            autoplay: { 
                delay: 2500,
                disableOnInteraction: false 
            },
            breakpoints: {
                576: { slidesPerView: 3 },
                768: { slidesPerView: 4 },
                1024: { slidesPerView: 6 }
            }
        });


        // --- LÓGICA DO HEADER & MENUS ---
        const header = document.querySelector('#mainHeader');
        const menuMobile = document.getElementById('instNavbarMobile');
        const toggler = document.querySelector('.navbar-toggler');

        // Efeito de Scroll no Header
        window.addEventListener('scroll', () => {
            if (header) {
                window.scrollY > 50 ? header.classList.add('scrolled') : header.classList.remove('scrolled');
            }
        });

        // Botão Mobile (Toggle)
        if (toggler && menuMobile) {
            toggler.addEventListener('click', function() {
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(menuMobile);
                menuMobile.classList.contains('show') ? bsCollapse.hide() : bsCollapse.show();
            });
        }

        // Dropdowns (Desktop Hover / Mobile Click)
        const dropdowns = document.querySelectorAll('.inst-dropdown-mega, .dropdown');
        dropdowns.forEach(drop => {
            const menu = drop.querySelector('.dropdown-menu');
            const toggle = drop.querySelector('.dropdown-toggle');

            if (window.innerWidth > 991) {
                let timeout;
                drop.addEventListener('mouseenter', () => {
                    clearTimeout(timeout);
                    menu.classList.add('show');
                });
                drop.addEventListener('mouseleave', () => {
                    timeout = setTimeout(() => menu.classList.remove('show'), 200);
                });
            } else if (toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    menu.classList.toggle('show');
                });
            }
        });

        // Fechar menu mobile ao clicar em links (Âncoras)
        const navLinks = document.querySelectorAll('.nav-link:not(.dropdown-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992 && menuMobile.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(menuMobile);
                    if (bsCollapse) bsCollapse.hide();
                }
            });
        });


        // --- EFEITOS VISUAIS E PARALLAX ---

        // Banner Parallax Suave (Seção Sobre)
        window.addEventListener('scroll', function() {
            const parallax = document.querySelector('.parallax-img');
            if (parallax) {
                let scrollPosition = window.pageYOffset;
                parallax.style.transform = 'translateY(' + (scrollPosition * 0.15) + 'px)';
            }
        });

        // Contadores Numéricos
        const counters = document.querySelectorAll('.stat-number, .counter');
        const startCounters = () => {
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const updateCount = () => {
                    const count = +counter.innerText;
                    const inc = target / 100;
                    if (count < target) {
                        counter.innerText = Math.ceil(count + inc);
                        setTimeout(updateCount, 15);
                    } else {
                        counter.innerText = target;
                    }
                };
                updateCount();
            });
        };

        let counted = false;
        window.addEventListener('scroll', () => {
            const section = document.querySelector('.stats-section, .counter-section');
            if (section && !counted) {
                const rect = section.getBoundingClientRect();
                if (rect.top <= window.innerHeight) {
                    startCounters();
                    counted = true;
                }
            }
        });

        // Interação de Opacidade no Footer
        const footerLinks = document.querySelectorAll('.social-icon, .footer-links a');
        footerLinks.forEach(link => {
            link.addEventListener('mouseenter', () => {
                footerLinks.forEach(other => {
                    if (other !== link) other.style.opacity = '0.3';
                });
            });
            link.addEventListener('mouseleave', () => {
                footerLinks.forEach(other => other.style.opacity = '1');
            });
        });

    });
</script>