// ============================
// APP-CUSTOM.JS
// Scripts globales del frontend público.
// Se carga en todas las rutas excepto checkout.
// ============================


// ======== Back to Top ========
document.addEventListener('DOMContentLoaded', function() {
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.style.display = window.scrollY > 100 ? 'block' : 'none';
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});


// ======== setFormType ========
// Usado en contact/form.blade.php
function setFormType(type) {
    const contactType = document.getElementById('contact_type');
    if (!contactType) return;

    contactType.value = type;

    document.querySelectorAll('.form-field').forEach(el => {
        const types = el.dataset.type.split(' ');
        const show = types.includes(String(type));
        el.style.display = show ? 'block' : 'none';
        el.querySelectorAll('input, textarea').forEach(input => input.required = show);
    });

    document.querySelector('input[name="name"]').required = true;
    document.querySelector('input[name="email"]').required = true;
}
setFormType(1);


// ======== Bootstrap Carousel Touch Swipe ========
document.querySelectorAll('.carousel').forEach(carousel => {
    let startX = 0,
        endX = 0;
    carousel.addEventListener('touchstart', e => startX = e.touches[0].clientX);
    carousel.addEventListener('touchend', e => {
        endX = e.changedTouches[0].clientX;
        if (startX - endX > 50) bootstrap.Carousel.getInstance(carousel).next();
        if (endX - startX > 50) bootstrap.Carousel.getInstance(carousel).prev();
    });
});


// ======== Blog Swiper ========
document.addEventListener('DOMContentLoaded', function() {
    const blogSwiper = new Swiper('.blogSwiper', {
        slidesPerView: 3,
        spaceBetween: 20,
        loop: true,
        grabCursor: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            320: {
                slidesPerView: 1
            },
            576: {
                slidesPerView: 1
            },
            768: {
                slidesPerView: 2
            },
            992: {
                slidesPerView: 3
            },
        }
    });
});
