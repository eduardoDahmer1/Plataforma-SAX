document.addEventListener('DOMContentLoaded', function () {

    // Embaralha aleatoriamente a ordem das imagens a cada carregamento de página
    // (banners do topo, marcas e galeria) — precisa rodar antes do Swiper/AOS lerem o DOM.
    function shuffleChildren(container) {
        if (!container) return;
        const items = Array.from(container.children);
        for (let i = items.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [items[i], items[j]] = [items[j], items[i]];
        }
        items.forEach(item => container.appendChild(item));
    }

    shuffleChildren(document.querySelector('.mainSwiper .swiper-wrapper'));
    shuffleChildren(document.querySelector('.brandsSwiper .swiper-wrapper'));
    shuffleChildren(document.getElementById('institucionalGalleryGrid'));

    // Sorteia uma imagem diferente para cada fundo (parallax, stats, cta), evitando repetir
    // a mesma foto entre eles quando há variedade suficiente no pool disponível.
    (function randomizeScenery() {
        const used = [];
        document.querySelectorAll('[data-scenery-pool]').forEach((el) => {
            let pool;
            try {
                pool = JSON.parse(el.getAttribute('data-scenery-pool'));
            } catch (e) {
                pool = [];
            }
            if (!Array.isArray(pool) || !pool.length) return;

            const available = pool.filter((url) => used.indexOf(url) === -1);
            const candidates = available.length ? available : pool;
            const pick = candidates[Math.floor(Math.random() * candidates.length)];
            used.push(pick);

            if (el.tagName === 'IMG') {
                el.src = pick;
            } else {
                el.style.backgroundImage = `url('${pick}')`;
            }
        });
    })();

    AOS.init({ duration: 1000, once: true });

    Fancybox.bind('[data-fancybox]', {
        activeClassName: 'fancybox-active',
        dragToClose: true,
        Toolbar: {
            display: {
                left:   ['infobar'],
                middle: [],
                right:  ['iterateZoom', 'slideshow', 'fullScreen', 'download', 'thumbs', 'close'],
            },
        },
        Images: { initialSize: 'fit' },
    });

    new Swiper('.mainSwiper', {
        loop: true,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: 1500,
        autoplay: { delay: 6000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    });

    new Swiper('.brandsSwiper', {
        slidesPerView: 2,
        spaceBetween: 40,
        loop: true,
        autoplay: { delay: 2500, disableOnInteraction: false },
        breakpoints: {
            576:  { slidesPerView: 3 },
            768:  { slidesPerView: 4 },
            1024: { slidesPerView: 6 },
        },
    });

    const header = document.querySelector('#mainHeader');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        }, { passive: true });
    }

    const parallax = document.querySelector('.parallax-img');
    if (parallax) {
        window.addEventListener('scroll', () => {
            parallax.style.transform = `translateY(${window.pageYOffset * 0.15}px)`;
        }, { passive: true });
    }

    const counters = document.querySelectorAll('.stat-number, .counter');
    if (counters.length) {
        let counted = false;
        const section = document.querySelector('.stats-section, .counter-section');

        const startCounters = () => {
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const inc = target / 100;
                const tick = () => {
                    const current = +counter.textContent;
                    if (current < target) {
                        counter.textContent = Math.ceil(current + inc);
                        setTimeout(tick, 15);
                    } else {
                        counter.textContent = target;
                    }
                };
                tick();
            });
        };

        if (section) {
            window.addEventListener('scroll', () => {
                if (!counted && section.getBoundingClientRect().top <= window.innerHeight) {
                    startCounters();
                    counted = true;
                }
            }, { passive: true });
        }
    }

    const footerLinks = document.querySelectorAll('.social-icon, .footer-links a');
    if (footerLinks.length) {
        footerLinks.forEach(link => {
            link.addEventListener('mouseenter', () => {
                footerLinks.forEach(other => { if (other !== link) other.style.opacity = '0.3'; });
            });
            link.addEventListener('mouseleave', () => {
                footerLinks.forEach(other => { other.style.opacity = '1'; });
            });
        });
    }

    if (typeof $ !== 'undefined') {
        $('.video-responsive-container iframe').attr({ width: '100%', height: '100%' });

        $('#modalCDE').on('hidden.bs.modal', function () {
            const $iframe = $(this).find('iframe');
            const src = $iframe.attr('src');
            $iframe.attr('src', '').attr('src', src);
        });
    }

});
