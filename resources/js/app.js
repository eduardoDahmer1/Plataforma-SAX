import './bootstrap';

/** ========================
 * Swiper JS Init
 ========================== */
 document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swiper !== 'undefined') {
        new Swiper(".mySwiper", {
            loop: true,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });
    }
});

/** ========================
 * Splide JS Init
 ========================== */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Splide !== 'undefined') {
        new Splide('#splide', {
            type: 'loop',
            perPage: 1,
            autoplay: true,
        }).mount();
    }
});

/** ========================
 * Glide JS Init
 ========================== */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Glide !== 'undefined') {
        new Glide('.glide', {
            type: 'carousel',
            autoplay: 2000,
        }).mount();
    }
});

/** ========================
 * lightGallery Init
 ========================== */
document.addEventListener('DOMContentLoaded', function () {
    if (typeof lightGallery !== 'undefined') {
        lightGallery(document.getElementById('lightgallery'), {
            plugins: [lgZoom],
            speed: 500,
        });
    }
});