// ============================
// HOME.JS
// Scripts de la página principal (home) y componentes de home.
// Se carga en rutas: home, produto.show
// ============================


// ======== Swiper: Productos (.mySwiper) ========
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper === 'undefined') return;

    new Swiper(".mySwiper", {
        slidesPerView: 2,
        spaceBetween: 10,
        grabCursor: true,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            768: {
                slidesPerView: 3,
                spaceBetween: 15
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 20
            },
            1400: {
                slidesPerView: 5,
                spaceBetween: 20
            }
        }
    });

    // Blog Swiper
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
        }
    });

    // Product Swiper (destaques)
    new Swiper(".productSwiper", {
        slidesPerView: 2,
        spaceBetween: 20,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            768: { slidesPerView: 3 },
            1024: { slidesPerView: 5 },
        }
    });

    // Toggle Accordion SAX Style
    document.querySelectorAll('.accordion-trigger').forEach(trigger => {
        trigger.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('i');

            content.classList.toggle('show');

            if (content.classList.contains('show')) {
                icon.classList.replace('fa-plus', 'fa-minus');
            } else {
                icon.classList.replace('fa-minus', 'fa-plus');
            }
        });
    });
});


// ======== Swiper: Main Slider Hero (.mainSwiper) ========
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swiper === 'undefined') return;

    var mainSwiperEl = document.querySelector('.mainSwiper');
    if (!mainSwiperEl) return;

    new Swiper('.mainSwiper', {
        loop: true,
        speed: 800,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
    });
});


// ======== Brands Grid: Carrusel 3D ========
// Requiere window.saxBrandsData (inyectado desde brands-grid.blade.php)
(function() {
    window.addEventListener('load', function() {
        var brands = window.saxBrandsData;
        if (!brands || brands.length === 0) return;

        var container = document.getElementById('brandsCarousel');
        var nameDisplay = document.getElementById('saxBrandName');
        var dotsContainer = document.getElementById('saxDots');
        if (!container || !nameDisplay || !dotsContainer) return;

        var currentIndex = 0;

        // Obtener baseUrl y marcasUrl de data-attributes
        var storageBase = container.getAttribute('data-storage-base') || '/storage';
        var marcasUrl = container.getAttribute('data-marcas-url') || '/marcas';

        brands.forEach(function(brand, i) {
            var div = document.createElement('div');
            div.className = 'sax-item hidden';
            div.setAttribute('data-name', brand.name);

            var imgFile = (brand.banner || '').replace(/^\/+/, '');
            var imgPath = imgFile.startsWith('http')
                ? imgFile
                : storageBase + '/' + imgFile;

            div.innerHTML =
                '<a href="' + marcasUrl + '/' + (brand.slug || brand.id) + '">' +
                    '<img src="' + imgPath + '" alt="' + brand.name + '" ' +
                         'onerror="this.src=\'https://placehold.co/320x480/222/fff?text=' + brand.name.replace(/\s/g, '+') + '\'">' +
                '</a>';
            container.appendChild(div);

            var dot = document.createElement('div');
            dot.className = 'sax-dot';
            dotsContainer.appendChild(dot);
        });

        var items = container.querySelectorAll('.sax-item');
        var dots = dotsContainer.querySelectorAll('.sax-dot');

        function updateCarousel() {
            if (items.length === 0) return;

            items.forEach(function(item, i) {
                item.className = 'sax-item hidden';
                if (dots[i]) dots[i].classList.remove('active');

                var diff = i - currentIndex;

                if (diff > brands.length / 2) diff -= brands.length;
                if (diff < -brands.length / 2) diff += brands.length;

                if (diff === 0) {
                    item.className = 'sax-item active';
                    nameDisplay.innerText = item.getAttribute('data-name');
                    if (dots[i]) dots[i].classList.add('active');
                }
                else if (diff === -1) item.className = 'sax-item p1';
                else if (diff === 1) item.className = 'sax-item n1';
                else if (diff === -2) item.className = 'sax-item p2';
                else if (diff === 2) item.className = 'sax-item n2';
            });
        }

        document.getElementById('saxNext').addEventListener('click', function() {
            currentIndex = (currentIndex + 1) % brands.length;
            updateCarousel();
        });

        document.getElementById('saxPrev').addEventListener('click', function() {
            currentIndex = (currentIndex - 1 + brands.length) % brands.length;
            updateCarousel();
        });

        items.forEach(function(item, index) {
            item.addEventListener('click', function(e) {
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
