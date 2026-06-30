AOS.init({ duration: 1000, once: true, easing: 'ease-out-back' });

const header = document.getElementById('mainHeader');
if (header) {
    window.addEventListener('scroll', () => {
        header.classList.toggle('scrolled', window.scrollY > 50);
    }, { passive: true });
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.mySwiper')) {
        new Swiper('.mySwiper', {
            effect: 'fade',
            loop: true,
            autoplay: { delay: 5000 },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    }
});
