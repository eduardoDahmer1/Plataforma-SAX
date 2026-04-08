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


// ======== Blog Show: Progress Bar + Parallax ========
document.addEventListener('DOMContentLoaded', function () {
    var bar = document.querySelector('.reading-progress-bar');
    var parallax = document.querySelector('.hero-parallax');
    if (!bar) return;

    window.addEventListener('scroll', function () {
        var winScroll = document.documentElement.scrollTop;
        var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        bar.style.width = ((winScroll / height) * 100) + '%';
        if (parallax) parallax.style.transform = 'translateY(' + (window.pageYOffset * 0.15) + 'px)';
    });
});

// ======== Blog Show: Copy Link ========
function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href).then(function () {
        alert('Link copiado!');
    });
}


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


// ======== Header: Drawer Menu + Search Mobile + Accordion ========
document.addEventListener('DOMContentLoaded', function() {
    // --- Drawer Menu ---
    const btnOpen = document.getElementById('mobileMenuBtn');
    const btnClose = document.getElementById('closeDrawer');
    const drawer = document.getElementById('saxDrawer');
    const overlay = document.getElementById('drawerOverlay');

    function toggleDrawer() {
        drawer.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.style.overflow = drawer.classList.contains('active') ? 'hidden' : '';
    }

    btnOpen?.addEventListener('click', toggleDrawer);
    btnClose?.addEventListener('click', toggleDrawer);
    overlay?.addEventListener('click', toggleDrawer);

    // --- Search Mobile ---
    const btnSearchOpen = document.getElementById('mobileSearchBtn');
    const btnSearchClose = document.getElementById('closeSearch');
    const searchOverlay = document.getElementById('mobileSearchOverlay');
    const searchInput = document.getElementById('mobileSearchInput');

    btnSearchOpen?.addEventListener('click', () => {
        searchOverlay.classList.add('active');
        setTimeout(() => searchInput.focus(), 300);
        document.body.style.overflow = 'hidden';
    });

    btnSearchClose?.addEventListener('click', () => {
        searchOverlay.classList.remove('active');
        document.body.style.overflow = '';
    });

    // --- Accordion Logic ---
    document.querySelectorAll('.toggle-sub').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            targetEl.classList.toggle('d-none');
            this.querySelector('i').classList.toggle('fa-chevron-up');
            this.querySelector('i').classList.toggle('fa-chevron-down');
        });
    });
});


// ======== Carrinho Header: Toggle Sidebar ========
document.addEventListener('DOMContentLoaded', function() {
    const cartBtn = document.getElementById('cart-button');
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    const cartClose = document.getElementById('cart-close');

    function toggleCart() {
        cartSidebar.classList.toggle('open');
        cartOverlay.classList.toggle('open');
        document.body.style.overflow = cartSidebar.classList.contains('open') ? 'hidden' : '';
    }

    cartBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        toggleCart();
    });

    cartClose?.addEventListener('click', toggleCart);
    cartOverlay?.addEventListener('click', toggleCart);
});


// ======== Modal Login: Tabs + Password Toggle + Generador ========
document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const forgotForm = document.getElementById('forgotForm');
    const modalTitle = document.getElementById('modalTitle');

    if (!loginForm) return;

    function showForm(form) {
        [loginForm, registerForm, forgotForm].forEach(f => f.classList.add('d-none'));
        form.classList.remove('d-none');

        const lang = window.saxLang || {};
        switch(form.id) {
            case 'loginForm': modalTitle.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>' + lang.entrar; break;
            case 'registerForm': modalTitle.innerHTML = '<i class="fas fa-user-plus me-2"></i>' + lang.cadastrar; break;
            case 'forgotForm': modalTitle.innerHTML = '<i class="fas fa-envelope me-2"></i>' + lang.recuperar_senha; break;
        }
    }

    // Eventos de Troca
    document.getElementById('showRegister').addEventListener('click', e => { e.preventDefault(); showForm(registerForm); });
    document.getElementById('showLogin').addEventListener('click', e => { e.preventDefault(); showForm(loginForm); });
    document.getElementById('showForgot').addEventListener('click', e => { e.preventDefault(); showForm(forgotForm); });
    document.getElementById('showLoginFromForgot').addEventListener('click', e => { e.preventDefault(); showForm(loginForm); });

    // Gerador de senha
    document.getElementById('generatePassword')?.addEventListener('click', function () {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*";
        let pass = "";
        for (let i = 0; i < 12; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));

        document.getElementById('register_password').value = pass;
        document.getElementById('password_confirmation').value = pass;
        document.getElementById('register_password').type = 'text';
        alert("Senha sugerida: " + pass);
    });

    // Mostrar/Ocultar senha
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = document.getElementById(this.dataset.target);
            const icon = this.querySelector('i');
            if(target.type === "password") {
                target.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                target.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
});


// ======== User Profile: Toggle SAX Registration Field ========
document.addEventListener('DOMContentLoaded', function() {
    const alreadyRegistered = document.getElementById('already_registered');
    if (!alreadyRegistered) return;

    alreadyRegistered.addEventListener('change', function() {
        document.getElementById('sax_number_field').style.display = this.value === '1' ? 'block' : 'none';
    });
});


// ======== Forgot Password: Focus Icons + Submit Feedback (jQuery) ========
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ === 'undefined') return;

    $(".input").focusin(function() {
        $(this).find("span").animate({"opacity": "0"}, 200);
    });

    $(".input").focusout(function() {
        $(this).find("span").animate({"opacity": "1"}, 300);
    });

    $(".login").submit(function() {
        $(this).find(".submit i").removeAttr('class').addClass("fa fa-check").css({"color": "#fff"});
        $(".submit").css({"background": "#2ecc71", "border-color": "#2ecc71"});
        $(".feedback").show().animate({"opacity": "1", "bottom": "-80px"}, 400);
        $("input").css({"border-color": "#2ecc71"});
        return false;
    });
});
