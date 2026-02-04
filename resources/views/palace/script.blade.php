<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
    // Inicialização de Animações
    AOS.init({
        duration: 1000,
        once: true,
        easing: 'ease-out-back'
    });

    // Efeito Header ao Rolas
    window.addEventListener('scroll', function() {
        const header = document.getElementById('mainHeader');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Controle do Swiper (Destaques)
    document.addEventListener('DOMContentLoaded', function() {
        if(document.querySelector('.mySwiper')) {
            const swiper = new Swiper(".mySwiper", {
                effect: "fade",
                loop: true,
                autoplay: { delay: 5000 },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                }
            });
        }
    });
</script>