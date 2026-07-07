AOS.init({ duration: 900, once: true, easing: 'ease-out-cubic', offset: 80 });

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('DOMContentLoaded', function () {
    const header = document.getElementById('mainHeader');
    const revealItems = document.querySelectorAll('.palace-reveal');

    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 40);
        }, { passive: true });
    }

    if (!prefersReducedMotion && 'IntersectionObserver' in window && revealItems.length) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });

        revealItems.forEach((item) => revealObserver.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    if (!prefersReducedMotion) {
        document.querySelectorAll('[data-palace-tilt]').forEach((card) => {
            const reset = () => {
                card.style.setProperty('--tilt-x', '0deg');
                card.style.setProperty('--tilt-y', '0deg');
                card.style.setProperty('--shine-x', '50%');
                card.style.setProperty('--shine-y', '50%');
                card.style.transform = '';
            };

            card.addEventListener('mousemove', (event) => {
                const rect = card.getBoundingClientRect();
                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;
                const rotateY = ((x / rect.width) - 0.5) * 6;
                const rotateX = ((0.5 - (y / rect.height)) * 6);

                card.style.setProperty('--shine-x', `${(x / rect.width) * 100}%`);
                card.style.setProperty('--shine-y', `${(y / rect.height) * 100}%`);
                card.style.transform = `perspective(1200px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-3px)`;
            });

            card.addEventListener('mouseleave', reset);
            card.addEventListener('blur', reset);
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (event) => {
            const targetId = anchor.getAttribute('href');
            if (!targetId || targetId === '#') {
                return;
            }

            const target = document.querySelector(targetId);

            if (!target) {
                return;
            }

            event.preventDefault();
            target.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
        });
    });

    if (document.querySelector('.mySwiper')) {
        new Swiper('.mySwiper', {
            effect: 'fade',
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    }
});
