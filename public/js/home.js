document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swiper === 'undefined') return;

    new Swiper('.mySwiper', {
        slidesPerView: 2,
        spaceBetween: 10,
        grabCursor: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            768:  { slidesPerView: 3, spaceBetween: 15 },
            1024: { slidesPerView: 4, spaceBetween: 20 },
            1400: { slidesPerView: 5, spaceBetween: 20 },
        },
    });

    new Swiper('.blogSwiper', {
        slidesPerView: 3,
        spaceBetween: 20,
        loop: true,
        grabCursor: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            320: { slidesPerView: 1 },
            576: { slidesPerView: 1 },
            768: { slidesPerView: 2 },
            992: { slidesPerView: 3 },
        },
    });

    new Swiper('.productSwiper', {
        slidesPerView: 2,
        spaceBetween: 20,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            768:  { slidesPerView: 3 },
            1024: { slidesPerView: 5 },
        },
    });

    const mainSwiperEl = document.querySelector('.mainSwiper');
    if (mainSwiperEl) {
        new Swiper('.mainSwiper', {
            loop: true,
            speed: 800,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-nav-click.next',
                prevEl: '.swiper-nav-click.prev',
            },
        });
    }

    document.querySelectorAll('.accordion-trigger').forEach(trigger => {
        trigger.addEventListener('click', function () {
            const content = this.nextElementSibling;
            const icon    = this.querySelector('i');
            content.classList.toggle('show');
            icon.classList.replace(
                content.classList.contains('show') ? 'fa-plus' : 'fa-minus',
                content.classList.contains('show') ? 'fa-minus' : 'fa-plus'
            );
        });
    });
});

(function () {
    window.addEventListener('load', function () {
        const brands       = window.saxBrandsData;
        if (!brands || brands.length === 0) return;

        const container    = document.getElementById('brandsCarousel');
        const nameDisplay  = document.getElementById('saxBrandName');
        const dotsContainer = document.getElementById('saxDots');
        if (!container || !nameDisplay || !dotsContainer) return;

        const storageBase    = container.dataset.storageBase   || '/storage';
        const marcasUrl      = container.dataset.marcasUrl     || '/marcas';
        const fallbackBanner = container.dataset.fallbackBanner || `${storageBase}/uploads/banner_horizontal.webp`;

        const normalizeImagePath = (rawPath) => {
            if (!rawPath || !String(rawPath).trim()) return fallbackBanner;

            const clean = String(rawPath).trim().replace(/^\/+/, '');
            if (/^https?:\/\//i.test(clean)) return clean;

            const withoutStoragePrefix = clean.replace(/^storage\//i, '');
            return `${storageBase}/${withoutStoragePrefix}`;
        };

        brands.forEach((brand, i) => {
            const imgPath = normalizeImagePath(brand.banner);

            const div = document.createElement('div');
            div.className = 'sax-item hidden';
            div.dataset.name = brand.name;
            div.innerHTML = `<a href="${marcasUrl}/${brand.slug || brand.id}"><img src="${imgPath}" alt="${brand.name}" loading="lazy" decoding="async" onerror="this.src='${fallbackBanner}'"></a>`;
            container.appendChild(div);

            const dot = document.createElement('div');
            dot.className = 'sax-dot';
            dotsContainer.appendChild(dot);
        });

        const items = container.querySelectorAll('.sax-item');
        const dots  = dotsContainer.querySelectorAll('.sax-dot');
        let currentIndex = 0;

        function updateCarousel() {
            if (!items.length) return;
            const half = brands.length / 2;

            items.forEach((item, i) => {
                item.className = 'sax-item hidden';
                dots[i]?.classList.remove('active');

                let diff = i - currentIndex;
                if (diff >  half) diff -= brands.length;
                if (diff < -half) diff += brands.length;

                if      (diff ===  0) { item.className = 'sax-item active'; nameDisplay.textContent = item.dataset.name; dots[i]?.classList.add('active'); }
                else if (diff === -1) item.className = 'sax-item p1';
                else if (diff ===  1) item.className = 'sax-item n1';
                else if (diff === -2) item.className = 'sax-item p2';
                else if (diff ===  2) item.className = 'sax-item n2';
            });
        }

        document.getElementById('saxNext').addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % brands.length;
            updateCarousel();
        });

        document.getElementById('saxPrev').addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + brands.length) % brands.length;
            updateCarousel();
        });

        items.forEach((item, index) => {
            item.addEventListener('click', e => {
                if (!item.classList.contains('active')) {
                    e.preventDefault();
                    currentIndex = index;
                    updateCarousel();
                }
            });
        });

        updateCarousel();
    });
})();
