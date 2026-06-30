// ── Accordion ───────────────────────────────────────────────
document.querySelectorAll('.accordion-trigger').forEach(trigger => {
    trigger.addEventListener('click', function () {
        const content = this.nextElementSibling;
        const icon    = this.querySelector('i');
        content.classList.toggle('show');
        icon.classList.replace(
            content.classList.contains('show') ? 'fa-plus' : 'fa-minus',
            content.classList.contains('show') ? 'fa-minus' : 'fa-plus'
        );
    });
});

// ── Bootstrap Carousel: Touch Swipe ─────────────────────────
document.querySelectorAll('.carousel').forEach(carousel => {
    let startX = 0;
    carousel.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
    carousel.addEventListener('touchend', e => {
        const diff = startX - e.changedTouches[0].clientX;
        const instance = bootstrap.Carousel.getInstance(carousel);
        if (diff >  50) instance?.next();
        if (diff < -50) instance?.prev();
    });
});

// ── Global: setFormType (contact form) ──────────────────────
function setFormType(type) {
    const contactType = document.getElementById('contact_type');
    if (!contactType) return;

    contactType.value = type;

    document.querySelectorAll('.btn-sax-tab').forEach(btn => btn.classList.remove('active'));
    document.getElementById(type === 1 ? 'btn-atendimento' : 'btn-curriculo')?.classList.add('active');

    document.querySelectorAll('.form-field').forEach(el => {
        const show = el.dataset.type.split(' ').includes(String(type));
        el.style.display = show ? 'block' : 'none';
        el.querySelectorAll('input, textarea').forEach(input => { input.required = show; });
    });

    document.querySelector('input[name="name"]').required  = true;
    document.querySelector('input[name="email"]').required = true;
}
setFormType(1);

// ── Global: copyToClipboard (blog share) ────────────────────
function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href).then(() => alert('Link copiado!'));
}

// ── DOMContentLoaded ────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // Back to Top
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            backToTop.style.display = window.scrollY > 100 ? 'block' : 'none';
        }, { passive: true });
        backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // Blog: Progress Bar + Parallax
    const bar      = document.querySelector('.reading-progress-bar');
    const parallax = document.querySelector('.hero-parallax');
    if (bar) {
        window.addEventListener('scroll', () => {
            const h = document.documentElement;
            bar.style.width = `${(h.scrollTop / (h.scrollHeight - h.clientHeight)) * 100}%`;
            if (parallax) parallax.style.transform = `translateY(${window.pageYOffset * 0.15}px)`;
        }, { passive: true });
    }

    // Blog Swiper
    if (document.querySelector('.blogSwiper')) {
        new Swiper('.blogSwiper', {
            slidesPerView: 3,
            spaceBetween: 20,
            loop: true,
            grabCursor: true,
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            breakpoints: {
                320: { slidesPerView: 1 },
                576: { slidesPerView: 1 },
                768: { slidesPerView: 2 },
                992: { slidesPerView: 3 },
            },
        });
    }

    // Drawer Menu
    const drawer  = document.getElementById('saxDrawer');
    const overlay = document.getElementById('drawerOverlay');
    if (drawer && overlay) {
        const toggleDrawer = () => {
            drawer.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = drawer.classList.contains('active') ? 'hidden' : '';
        };
        document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleDrawer);
        document.getElementById('closeDrawer')?.addEventListener('click', toggleDrawer);
        overlay.addEventListener('click', toggleDrawer);
    }

    // Search Mobile
    const searchOverlay = document.getElementById('mobileSearchOverlay');
    const searchInput   = document.getElementById('mobileSearchInput');
    if (searchOverlay) {
        document.getElementById('mobileSearchBtn')?.addEventListener('click', () => {
            searchOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => searchInput?.focus(), 300);
        });
        document.getElementById('closeSearch')?.addEventListener('click', () => {
            searchOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Drawer Accordion
    document.querySelectorAll('.toggle-sub').forEach(btn => {
        btn.addEventListener('click', function () {
            const el   = document.getElementById(this.dataset.target);
            const icon = this.querySelector('i');
            el?.classList.toggle('d-none');
            icon?.classList.toggle('fa-chevron-up');
            icon?.classList.toggle('fa-chevron-down');
        });
    });

    // Cart Sidebar
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    if (cartSidebar && cartOverlay) {
        const toggleCart = () => {
            cartSidebar.classList.toggle('open');
            cartOverlay.classList.toggle('open');
            document.body.style.overflow = cartSidebar.classList.contains('open') ? 'hidden' : '';
        };
        document.getElementById('cart-button')?.addEventListener('click', e => { e.preventDefault(); toggleCart(); });
        document.getElementById('cart-close')?.addEventListener('click', toggleCart);
        cartOverlay.addEventListener('click', toggleCart);
    }

    // User Profile: SAX registration field
    const alreadyRegistered = document.getElementById('already_registered');
    if (alreadyRegistered) {
        alreadyRegistered.addEventListener('change', function () {
            document.getElementById('sax_number_field').style.display = this.value === '1' ? 'block' : 'none';
        });
    }

    // ── Auth Modal ───────────────────────────────────────────
    const loginForm    = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const forgotForm   = document.getElementById('forgotForm');
    const resetForm    = document.getElementById('resetForm');
    const loginModal   = document.getElementById('loginModal');
    const modalTitle   = document.getElementById('modalTitle');
    if (!loginForm) return;

    const authRedirectFields = document.querySelectorAll('[data-auth-redirect-field]');
    const authTabs           = document.querySelectorAll('[data-auth-tab]');
    const registerEmail                  = document.getElementById('register_email');
    const registerPassword               = document.getElementById('register_password');
    const registerPasswordConfirmation   = document.getElementById('password_confirmation');
    const registerEmailError             = document.getElementById('registerEmailError');
    const registerPasswordError          = document.getElementById('registerPasswordError');

    const isCompleteEmail = email => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    const setAuthRedirect = url => {
        authRedirectFields.forEach(f => { f.value = url || window.location.href; });
    };

    const showForm = form => {
        [loginForm, registerForm, forgotForm, resetForm].forEach(f => f.classList.add('d-none'));
        form.classList.remove('d-none');
        if (modalTitle) modalTitle.textContent = 'SAX';
        authTabs.forEach(tab => {
            tab.classList.toggle('is-active', tab.dataset.authTab === form.id);
        });
    };

    const showFieldError = (field, errorEl, message) => {
        if (!errorEl) return;
        errorEl.textContent  = message || '';
        errorEl.style.display = message ? 'block' : 'none';
        field?.classList.toggle('is-invalid', Boolean(message));
    };

    setAuthRedirect(window.location.href);

    loginModal?.addEventListener('show.bs.modal', e => {
        setAuthRedirect(e.relatedTarget?.dataset.redirectTo || window.location.href);
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
        bootstrap.Modal.getOrCreateInstance(loginModal).show();
        showForm(resetForm);
    }

    document.querySelectorAll('.js-requires-login').forEach(trigger => {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            setAuthRedirect(this.dataset.redirectTo || window.location.href);
            if (loginModal && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(loginModal).show();
                return;
            }
            window.location.href = this.href;
        });
    });

    document.getElementById('showRegister')?.addEventListener('click',        e => { e.preventDefault(); showForm(registerForm); });
    document.getElementById('showLogin')?.addEventListener('click',           e => { e.preventDefault(); showForm(loginForm); });
    document.getElementById('showForgot')?.addEventListener('click',         e => { e.preventDefault(); showForm(forgotForm); });
    document.getElementById('showLoginFromForgot')?.addEventListener('click', e => { e.preventDefault(); showForm(loginForm); });

    registerEmail?.addEventListener('input', function () {
        if (!this.value || isCompleteEmail(this.value.trim())) showFieldError(registerEmail, registerEmailError, '');
    });

    registerPasswordConfirmation?.addEventListener('input', function () {
        if (!this.value || this.value === registerPassword?.value) showFieldError(registerPasswordConfirmation, registerPasswordError, '');
    });

    registerForm?.addEventListener('submit', function (e) {
        const email        = registerEmail?.value.trim() || '';
        const password     = registerPassword?.value || '';
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
            e.preventDefault();
            (registerEmail?.classList.contains('is-invalid') ? registerEmail : registerPasswordConfirmation)?.focus();
        }
    });

    const ajaxForm = (form, { messageEl, btnEl, onSuccess }) => {
        form?.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const msg = document.getElementById(messageEl);
            const btn = document.getElementById(btnEl);

            if (btn) btn.disabled = true;
            if (msg) { msg.style.display = 'none'; msg.textContent = ''; }

            fetch(this.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': formData.get('_token') },
                body: formData,
            })
            .then(res => {
                if (res.status === 419) {
                    if (msg) { msg.textContent = 'Sua sessão expirou. Recarregando…'; msg.style.display = 'block'; msg.className = 'small mb-3 text-danger'; }
                    setTimeout(() => location.reload(), 1500);
                    return null;
                }
                if (res.status === 429) {
                    if (msg) { msg.textContent = 'Muitas tentativas. Aguarde e tente novamente.'; msg.style.display = 'block'; }
                    if (btn) btn.disabled = false;
                    return null;
                }
                return res.json();
            })
            .then(data => { if (data) onSuccess(data, msg, btn); })
            .catch(() => {
                if (msg) { msg.textContent = 'Erro inesperado. Tente novamente.'; msg.style.display = 'block'; msg.className = 'small mb-3 text-danger'; }
                if (btn) btn.disabled = false;
            });
        });
    };

    ajaxForm(loginForm, {
        messageEl: 'loginError',
        btnEl: null,
        onSuccess(data, msg, _btn) {
            const submitBtn = loginForm.querySelector('[type="submit"]');
            if (data.success) {
                window.location.href = data.redirect || '/';
            } else {
                if (msg) { msg.textContent = window.saxLang?.dados_incorretos || 'Dados incorretos.'; msg.style.display = 'block'; }
                if (submitBtn) submitBtn.disabled = false;
            }
        },
    });

    ajaxForm(forgotForm, {
        messageEl: 'forgotMessage',
        btnEl: 'btnForgot',
        onSuccess(data, msg, btn) {
            if (msg) {
                msg.textContent   = data.message;
                msg.style.display = 'block';
                msg.className     = `small mb-3 ${data.success ? 'text-success' : 'text-danger'}`;
            }
            if (!data.success && btn) btn.disabled = false;
        },
    });

    ajaxForm(resetForm, {
        messageEl: 'resetMessage',
        btnEl: 'btnReset',
        onSuccess(data, msg, btn) {
            if (msg) {
                msg.textContent   = data.message;
                msg.style.display = 'block';
                msg.className     = `small mb-3 ${data.success ? 'text-success' : 'text-danger'}`;
            }
            if (data.success) {
                setTimeout(() => { window.location.href = '/'; }, 1500);
            } else if (btn) {
                btn.disabled = false;
            }
        },
    });

    // Password Generator
    document.getElementById('generatePassword')?.addEventListener('click', () => {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*';
        const pass  = Array.from({ length: 12 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
        document.getElementById('register_password').value         = pass;
        document.getElementById('password_confirmation').value     = pass;
        document.getElementById('register_password').type          = 'text';
        alert('Senha sugerida: ' + pass);
    });

    // Password Toggle
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = document.getElementById(this.dataset.target);
            const icon   = this.querySelector('i');
            const isPass = target.type === 'password';
            target.type = isPass ? 'text' : 'password';
            icon.classList.replace(isPass ? 'fa-eye' : 'fa-eye-slash', isPass ? 'fa-eye-slash' : 'fa-eye');
        });
    });

});
