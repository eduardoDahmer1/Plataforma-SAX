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


    // --- LÓGICA DO HEADER ---
    const header = document.querySelector('#mainHeader');

    // Efeito de Scroll no Header
    window.addEventListener('scroll', () => {
        if (header) {
            window.scrollY > 50 ? header.classList.add('scrolled') : header.classList.remove('scrolled');
        }
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


    // --- Video Component: Resize iframes + Stop on modal close ---
    if (typeof $ !== 'undefined') {
        $('.video-responsive-container iframe').each(function() {
            $(this).attr('width', '100%').attr('height', '100%');
        });

        $('#modalCDE').on('hidden.bs.modal', function () {
            var $iframe = $(this).find('iframe');
            var src = $iframe.attr('src');
            $iframe.attr('src', '');
            $iframe.attr('src', src);
        });
    }

});
