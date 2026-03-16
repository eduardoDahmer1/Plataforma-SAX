<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body>

    {{-- Header --}}
    @include('components.header')

    <main>
        @yield('content')
    </main>

    <!-- Botão Voltar ao Topo -->
    <button id="backToTop" class="btn btn-primary position-fixed"
        style="bottom:30px; right:1em; display:none; z-index:1050;width: 3em;">
        <i class="fa fa-arrow-up"></i>
    </button>

    {{-- Footer --}}
    @include('components.footer')

    {{-- Scripts principais --}}
    @include('components.scripts')
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
    const swiper = new Swiper(".mySwiper", {
        slidesPerView: 4,
        spaceBetween: 20,
        loop: true, // 🔄 loop infinito real
        grabCursor: true, // 🤚 arrastar com mouse
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            320: {
                slidesPerView: 1
            },
            768: {
                slidesPerView: 2
            },
            1024: {
                slidesPerView: 4
            },
        }
    });
    </script>
    <script>
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
    </script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa Swiper para os destaques
        new Swiper(".productSwiper", {
            slidesPerView: 2,
            spaceBetween: 20,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                768: {
                    slidesPerView: 3
                },
                1024: {
                    slidesPerView: 5
                }
            }
        });

        // Toggle Accordion SAX Style
        document.querySelectorAll('.accordion-trigger').forEach(trigger => {
            trigger.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const icon = this.querySelector('i');

                content.classList.toggle('show');

                // Muda o ícone de + para -
                if (content.classList.contains('show')) {
                    icon.classList.replace('fa-plus', 'fa-minus');
                } else {
                    icon.classList.replace('fa-minus', 'fa-plus');
                }
            });
        });
    });
    </script>

</body>

</html>