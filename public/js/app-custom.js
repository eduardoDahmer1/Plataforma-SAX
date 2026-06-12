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

    // Gerencia o sublinhado/estado ativo dos botões
    document.querySelectorAll('.btn-sax-tab').forEach(btn => btn.classList.remove('active'));
    if (type === 1) {
        document.getElementById('btn-atendimento')?.classList.add('active');
    } else if (type === 2) {
        document.getElementById('btn-curriculo')?.classList.add('active');
    }

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
    const resetForm = document.getElementById('resetForm');
    const modalTitle = document.getElementById('modalTitle');
    const loginModal = document.getElementById('loginModal');
    const authRedirectFields = document.querySelectorAll('[data-auth-redirect-field]');
    const authTabs = document.querySelectorAll('[data-auth-tab]');
    const registerEmail = document.getElementById('register_email');
    const registerPassword = document.getElementById('register_password');
    const registerPasswordConfirmation = document.getElementById('password_confirmation');
    const registerEmailError = document.getElementById('registerEmailError');
    const registerPasswordError = document.getElementById('registerPasswordError');

    if (!loginForm) return;

    function setAuthRedirect(url) {
        authRedirectFields.forEach(field => {
            field.value = url || window.location.href;
        });
    }

    function showForm(form) {
        [loginForm, registerForm, forgotForm, resetForm].forEach(f => f.classList.add('d-none'));
        form.classList.remove('d-none');

        if (modalTitle) modalTitle.textContent = 'SAX';

        authTabs.forEach(tab => {
            tab.classList.toggle('is-active', tab.dataset.authTab === form.id);
        });
    }

    function isCompleteEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showFieldError(field, errorEl, message) {
        if (!errorEl) return;

        errorEl.textContent = message || '';
        errorEl.style.display = message ? 'block' : 'none';
        field?.classList.toggle('is-invalid', Boolean(message));
    }

    setAuthRedirect(window.location.href);

    loginModal?.addEventListener('show.bs.modal', function(event) {
        const trigger = event.relatedTarget;
        setAuthRedirect(trigger?.dataset.redirectTo || window.location.href);
        showForm(window.saxAuthModalForm === 'register' ? registerForm : loginForm);
    });

    if (window.saxAuthModalForm === 'register') {
        showForm(registerForm);

        if (loginModal && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(loginModal).show();
        }
    }

    const urlParams  = new URLSearchParams(location.search);
    const openTarget = urlParams.get('open');

    if (openTarget === 'login' && loginModal && typeof bootstrap !== 'undefined') {
        history.replaceState(null, '', location.pathname);
        bootstrap.Modal.getOrCreateInstance(loginModal).show();
    }

    if (openTarget === 'reset' && loginModal && typeof bootstrap !== 'undefined') {
        document.getElementById('reset_token').value = urlParams.get('token') || '';
        document.getElementById('reset_email').value = urlParams.get('email') || '';
        history.replaceState(null, '', location.pathname);

        // OJO con el orden: .show() dispara 'show.bs.modal', que llama showForm(loginForm).
        // Por eso showForm(resetForm) va DESPUÉS, para que el último gane y quede el reset visible.
        bootstrap.Modal.getOrCreateInstance(loginModal).show();
        showForm(resetForm);
    }

    document.querySelectorAll('.js-requires-login').forEach(trigger => {
        trigger.addEventListener('click', function(event) {
            event.preventDefault();
            setAuthRedirect(this.dataset.redirectTo || window.location.href);

            if (loginModal && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(loginModal).show();
                return;
            }

            window.location.href = this.href;
        });
    });

    // Eventos de Troca
    document.getElementById('showRegister').addEventListener('click', e => { e.preventDefault(); showForm(registerForm); });
    document.getElementById('showLogin').addEventListener('click', e => { e.preventDefault(); showForm(loginForm); });
    document.getElementById('showForgot').addEventListener('click', e => { e.preventDefault(); showForm(forgotForm); });
    document.getElementById('showLoginFromForgot').addEventListener('click', e => { e.preventDefault(); showForm(loginForm); });

    registerEmail?.addEventListener('input', function () {
        if (!this.value || isCompleteEmail(this.value.trim())) {
            showFieldError(registerEmail, registerEmailError, '');
        }
    });

    registerPasswordConfirmation?.addEventListener('input', function () {
        if (!this.value || this.value === registerPassword?.value) {
            showFieldError(registerPasswordConfirmation, registerPasswordError, '');
        }
    });

    registerForm?.addEventListener('submit', function (event) {
        const email = registerEmail?.value.trim() || '';
        const password = registerPassword?.value || '';
        const confirmation = registerPasswordConfirmation?.value || '';
        let hasError = false;

        if (!isCompleteEmail(email)) {
            showFieldError(registerEmail, registerEmailError, 'Informe um e-mail completo, como nome@dominio.com.');
            hasError = true;
        }

        if (password && confirmation && password !== confirmation) {
            showFieldError(registerPasswordConfirmation, registerPasswordError, 'A confirmacao da senha nao confere.');
            hasError = true;
        }

        if (hasError) {
            event.preventDefault();
            (registerEmail?.classList.contains('is-invalid') ? registerEmail : registerPasswordConfirmation)?.focus();
        }
    });

    // Login via AJAX — evita redirect ao falhar
    loginForm?.addEventListener('submit', function (event) {
        event.preventDefault();

        const loginError = document.getElementById('loginError');
        const submitBtn  = this.querySelector('[type="submit"]');
        const formData   = new FormData(this);

        submitBtn.disabled         = true;
        loginError.style.display   = 'none';
        loginError.textContent     = '';

        fetch(this.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': formData.get('_token'),
            },
            body: formData,
        })
        .then(res => {
            // 419 = sessão/CSRF expirou. Recarrega para obter um token novo.
            if (res.status === 419) {
                loginError.textContent   = 'Sua sessão expirou. Recarregando a página…';
                loginError.style.display = 'block';
                setTimeout(() => location.reload(), 1500);
                return null;
            }
            return res.json();
        })
        .then(data => {
            if (!data) return; // 419 já tratado acima
            if (data.success) {
                window.location.href = data.redirect || '/';
            } else {
                loginError.textContent   = window.saxLang?.dados_incorretos || 'Dados incorretos.';
                loginError.style.display = 'block';
                submitBtn.disabled       = false;
            }
        })
        .catch(() => {
            loginError.textContent   = 'Erro inesperado. Tente novamente.';
            loginError.style.display = 'block';
            submitBtn.disabled       = false;
        });
    });

    // Forgot Password via AJAX — mostra feedback sem fechar o modal
    forgotForm?.addEventListener('submit', function (event) {
        event.preventDefault();

        const forgotMessage = document.getElementById('forgotMessage');
        const btnForgot     = document.getElementById('btnForgot');
        const formData      = new FormData(this);

        btnForgot.disabled           = true;
        forgotMessage.style.display  = 'none';
        forgotMessage.textContent    = '';

        fetch(this.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': formData.get('_token'),
            },
            body: formData,
        })
        .then(res => {
            // 419 = sessão/CSRF expirou. Recarrega para obter um token novo.
            if (res.status === 419) {
                forgotMessage.textContent   = 'Sua sessão expirou. Recarregando a página…';
                forgotMessage.style.display = 'block';
                forgotMessage.className     = 'small mb-3 text-danger';
                setTimeout(() => location.reload(), 1500);
                return null;
            }
            return res.json();
        })
        .then(data => {
            if (!data) return; // 419 já tratado acima
            forgotMessage.textContent   = data.message;
            forgotMessage.style.display = 'block';
            forgotMessage.className     = data.success
                ? 'small mb-3 text-success'
                : 'small mb-3 text-danger';

            if (!data.success) {
                btnForgot.disabled = false;
            }
        })
        .catch(() => {
            forgotMessage.textContent   = 'Erro inesperado. Tente novamente.';
            forgotMessage.style.display = 'block';
            forgotMessage.className     = 'small mb-3 text-danger';
            btnForgot.disabled          = false;
        });
    });

    // Reset Password via AJAX — define nova senha sem sair do modal
    resetForm?.addEventListener('submit', function (event) {
        event.preventDefault();

        const resetMessage = document.getElementById('resetMessage');
        const btnReset     = document.getElementById('btnReset');
        const formData     = new FormData(this);

        btnReset.disabled          = true;
        resetMessage.style.display = 'none';
        resetMessage.textContent   = '';

        fetch(this.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': formData.get('_token'),
            },
            body: formData,
        })
        .then(res => {
            // 419 = sessão expirou. Aqui NÃO recarregamos: a URL já foi limpa
            // (replaceState), então o token do link se perderia. Pedimos um novo link.
            if (res.status === 419) {
                resetMessage.textContent   = 'Sua sessão expirou. Solicite o link novamente.';
                resetMessage.style.display = 'block';
                resetMessage.className     = 'small mb-3 text-danger';
                btnReset.disabled          = false;
                return null;
            }
            return res.json();
        })
        .then(data => {
            if (!data) return; // 419 já tratado acima
            resetMessage.textContent   = data.message;
            resetMessage.style.display = 'block';
            resetMessage.className     = data.success
                ? 'small mb-3 text-success'
                : 'small mb-3 text-danger';

            if (data.success) {
                setTimeout(() => { window.location.href = '/'; }, 1500);
            } else {
                btnReset.disabled = false;
            }
        })
        .catch(() => {
            resetMessage.textContent   = 'Erro inesperado. Tente novamente.';
            resetMessage.style.display = 'block';
            resetMessage.className     = 'small mb-3 text-danger';
            btnReset.disabled          = false;
        });
    });

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
