document.addEventListener('DOMContentLoaded', function () {

    // --- Navbar scroll effect ---
    const nav = document.getElementById('mainHeader');
    if (nav) {
        window.addEventListener('scroll', function () {
            nav.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // --- Intersection Observer (reveal animations) ---
    const revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('[data-reveal]').forEach(function (el) {
        revealObserver.observe(el);
    });

    // --- Smooth scroll for anchors ---
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Products Carousel — múltiples instancias
    document.querySelectorAll('[data-products-swiper]').forEach(function (el) {
    var section = el.closest('.bridal-products-section');
     new Swiper(el, {
        slidesPerView: 2,
        spaceBetween: 10,
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
        breakpoints: {
            768:  { slidesPerView: 3, spaceBetween: 15 },
            1024: { slidesPerView: 4, spaceBetween: 20 },
            1400: { slidesPerView: 5, spaceBetween: 20 },
        },
        });
    });


    // --- Swiper: Promo Carousel ---
    if (document.querySelector('.promoSwiper')) {
        new Swiper('.promoSwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.promo-pagination',
                clickable: true,
            },
            breakpoints: {
                768: { slidesPerView: 2, spaceBetween: 24 },
                1200: { slidesPerView: 3, spaceBetween: 30 },
            }
        });
    }

    // --- Swiper: Locations Carousel ---
    if (document.querySelector('.locationSwiper')) {
        new Swiper('.locationSwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.location-pagination',
                clickable: true,
            },
            breakpoints: {
                768: { slidesPerView: 2, spaceBetween: 24 },
                992: { slidesPerView: 3, spaceBetween: 30 },
            }
        });
    }

    // --- Swiper: Testimonials Carousel ---
    if (document.querySelector('.testimonialSwiper')) {
        new Swiper('.testimonialSwiper', {
            slidesPerView: 1,
            spaceBetween: 24,
            loop: true,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.testimonial-pagination',
                clickable: true,
            },
            navigation: {
                prevEl: '.tq-nav-prev',
                nextEl: '.tq-nav-next',
            },
            breakpoints: {
                768: { slidesPerView: 2, spaceBetween: 30 },
                1200: { slidesPerView: 2, spaceBetween: 40 },
            }
        });
    }

});
