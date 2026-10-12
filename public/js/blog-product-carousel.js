(function () {
    'use strict';
    document.querySelectorAll('.blog-product-carousel').forEach(function (carousel) {
        const track = carousel.querySelector('.blog-product-carousel__track');
        const previous = carousel.querySelector('[data-blog-carousel-prev]');
        const next = carousel.querySelector('[data-blog-carousel-next]');
        function update() {
            previous.disabled = track.scrollLeft <= 1;
            next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 1;
        }
        function move(direction) {
            track.scrollBy({ left: direction * track.clientWidth * 0.85,
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        }
        previous.addEventListener('click', () => move(-1));
        next.addEventListener('click', () => move(1));
        track.addEventListener('scroll', update, { passive: true });
        if (typeof ResizeObserver !== 'undefined') new ResizeObserver(update).observe(track);
        else window.addEventListener('resize', update);
        update();
    });
})();
