const swiper = new Swiper(".mySwiper", {
    slidesPerView: 4,
    spaceBetween: 20,
    loop: true,
    grabCursor: true,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    breakpoints: {
        320: { slidesPerView: 1 },
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 4 },
    }
});

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
            320: { slidesPerView: 1 },
            576: { slidesPerView: 1 },
            768: { slidesPerView: 2 },
            992: { slidesPerView: 3 },
        }
    });

    // Inicializa Swiper para os destaques
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
