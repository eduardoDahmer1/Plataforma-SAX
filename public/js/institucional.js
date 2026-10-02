document.addEventListener('DOMContentLoaded', () => {
    const shuffleChildren = (container) => {
        if (!container) return;
        const items = Array.from(container.children);
        for (let i = items.length - 1; i > 0; i -= 1) {
            const j = Math.floor(Math.random() * (i + 1));
            [items[i], items[j]] = [items[j], items[i]];
        }
        items.forEach((item) => container.appendChild(item));
    };

    shuffleChildren(document.querySelector('.mainSwiper .swiper-wrapper'));
    shuffleChildren(document.querySelector('.brandsSwiper .swiper-wrapper'));
    shuffleChildren(document.getElementById('institucionalGalleryGrid'));

    const sceneryElements = Array.from(document.querySelectorAll('[data-random-scenery]'));
    const usedScenery = new Set();
    sceneryElements.forEach((element) => {
        let pool = [];
        try {
            pool = JSON.parse(element.dataset.sceneryPool || '[]');
        } catch (error) {
            pool = [];
        }
        if (!Array.isArray(pool) || pool.length === 0) return;

        const available = pool.filter((url) => !usedScenery.has(url));
        const candidates = available.length ? available : pool;
        const chosen = candidates[Math.floor(Math.random() * candidates.length)];
        usedScenery.add(chosen);

        if (element.tagName === 'IMG') element.src = chosen;
        else element.style.backgroundImage = `url("${chosen}")`;
    });

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (window.AOS) {
        AOS.init({
            duration: prefersReducedMotion ? 0 : 850,
            once: true,
            offset: 45,
            easing: 'ease-out-cubic',
        });
    }

    if (window.Fancybox) {
        Fancybox.bind('[data-fancybox]', {
            dragToClose: true,
            Images: { initialSize: 'fit' },
            Toolbar: {
                display: {
                    left: ['infobar'],
                    middle: [],
                    right: ['iterateZoom', 'slideshow', 'fullScreen', 'download', 'thumbs', 'close'],
                },
            },
        });
    }

    const mainSwiperElement = document.querySelector('.mainSwiper');
    if (mainSwiperElement && window.Swiper) {
        const autoplayDelay = Number(mainSwiperElement.dataset.autoplay) || 6000;
        new Swiper(mainSwiperElement, {
            loop: mainSwiperElement.querySelectorAll('.swiper-slide').length > 1,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            speed: prefersReducedMotion ? 0 : 1300,
            autoplay: prefersReducedMotion ? false : { delay: autoplayDelay, disableOnInteraction: false },
            keyboard: { enabled: true },
            pagination: { el: '.inst-hero .swiper-pagination', clickable: true },
            navigation: {
                nextEl: '.inst-hero .swiper-button-next',
                prevEl: '.inst-hero .swiper-button-prev',
            },
        });
    }

    const brandsSwiperElement = document.querySelector('.brandsSwiper');
    if (brandsSwiperElement && window.Swiper) {
        new Swiper(brandsSwiperElement, {
            slidesPerView: 2,
            spaceBetween: 32,
            loop: brandsSwiperElement.querySelectorAll('.swiper-slide').length > 5,
            speed: 700,
            autoplay: prefersReducedMotion ? false : { delay: 2200, disableOnInteraction: false },
            breakpoints: {
                576: { slidesPerView: 3 },
                768: { slidesPerView: 4 },
                1024: { slidesPerView: 6 },
            },
        });
    }

    const header = document.querySelector('#mainHeader');
    const syncHeader = () => header?.classList.toggle('scrolled', window.scrollY > 40);
    syncHeader();
    window.addEventListener('scroll', syncHeader, { passive: true });

    const counterElements = document.querySelectorAll('.counter[data-target]');
    if (counterElements.length) {
        const animateCounter = (counter) => {
            const target = Number(counter.dataset.target) || 0;
            if (prefersReducedMotion) {
                counter.textContent = target;
                return;
            }
            const start = performance.now();
            const duration = 1450;
            const tick = (time) => {
                const progress = Math.min((time - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                counter.textContent = Math.round(target * eased);
                if (progress < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        };

        const statsSection = document.querySelector('.inst-stats');
        const observer = new IntersectionObserver((entries, currentObserver) => {
            if (!entries.some((entry) => entry.isIntersecting)) return;
            counterElements.forEach(animateCounter);
            currentObserver.disconnect();
        }, { threshold: 0.28 });
        if (statsSection) observer.observe(statsSection);
    }

    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const target = document.querySelector(link.getAttribute('href'));
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth' });
        });
    });

    document.querySelectorAll('.inst-video-card__frame iframe').forEach((iframe) => {
        iframe.setAttribute('loading', 'lazy');
        iframe.setAttribute('title', iframe.getAttribute('title') || 'Conteúdo em vídeo SAX');
    });
});
