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

    // Sticky Header (Desktop + Mobile)
    const siteHeader = document.querySelector('.sax-header');
    const isAdminLayout = document.body.classList.contains('sax-admin-body');

    if (siteHeader && !isAdminLayout) {
        const stickyThreshold = 80;

        const applyStickyState = () => {
            const shouldStick = window.scrollY > stickyThreshold;
            siteHeader.classList.toggle('is-sticky', shouldStick);
            document.body.style.paddingTop = shouldStick ? `${siteHeader.offsetHeight}px` : '';
        };

        applyStickyState();
        window.addEventListener('scroll', applyStickyState, { passive: true });
        window.addEventListener('resize', () => {
            if (siteHeader.classList.contains('is-sticky')) {
                document.body.style.paddingTop = `${siteHeader.offsetHeight}px`;
            }
        });
    } else if (siteHeader) {
        // No painel, o header permanece no fluxo normal. Alterar sua altura durante
        // o scroll provocava realimentação entre scrollY e padding-top do body.
        siteHeader.classList.remove('is-sticky');
        document.body.style.removeProperty('padding-top');
    }

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
            const isOpen = drawer.classList.contains('active');
            document.body.style.overflow = isOpen ? 'hidden' : '';
            siteHeader?.classList.toggle('menu-open', isOpen);
            drawer.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            document.getElementById('mobileMenuBtn')?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };
        document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleDrawer);
        document.getElementById('closeDrawer')?.addEventListener('click', toggleDrawer);
        overlay.addEventListener('click', toggleDrawer);
    }

    // Search Mobile
    const searchOverlay = document.getElementById('mobileSearchOverlay');
    const searchInput   = document.getElementById('mobileSearchInput');
    if (searchOverlay) {
        const openSearch = () => {
            searchOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => searchInput?.focus(), 120);
        };

        const closeSearch = () => {
            searchOverlay.classList.remove('active');
            document.body.style.overflow = '';
        };

        document.getElementById('mobileSearchBtn')?.addEventListener('click', openSearch);
        document.getElementById('mobileSearchBar')?.addEventListener('click', openSearch);
        document.getElementById('mobileDockSearch')?.addEventListener('click', openSearch);
        document.getElementById('closeSearch')?.addEventListener('click', closeSearch);

        searchOverlay.addEventListener('click', e => {
            if (e.target === searchOverlay) {
                closeSearch();
            }
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                closeSearch();
            }
        });
    }

    document.getElementById('mobileDockCart')?.addEventListener('click', () => {
        document.getElementById('cart-button')?.click();
    });

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

    // TODO: Extraer carrito y notificaciones a un drawer reutilizable con un único
    // manejo de overlay, foco, Escape y bloqueo de scroll.
    const notificationsDrawer = document.getElementById('adminNotificationsDrawer');
    const notificationsOverlay = document.getElementById('adminNotificationsOverlay');
    const notificationsButton = document.getElementById('adminNotificationsButton');
    const notificationsClose = document.getElementById('adminNotificationsClose');

    // Cart Sidebar
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    if (cartSidebar && cartOverlay) {
        const toggleCart = () => {
            notificationsDrawer?.classList.remove('open');
            notificationsOverlay?.classList.remove('open');
            notificationsButton?.setAttribute('aria-expanded', 'false');
            notificationsDrawer?.setAttribute('aria-hidden', 'true');

            cartSidebar.classList.toggle('open');
            cartOverlay.classList.toggle('open');
            const isCartOpen = cartSidebar.classList.contains('open');
            document.body.classList.toggle('sax-cart-drawer-open', isCartOpen);
            cartSidebar.setAttribute('aria-hidden', isCartOpen ? 'false' : 'true');
            document.getElementById('cart-button')?.setAttribute('aria-expanded', isCartOpen ? 'true' : 'false');
            document.body.style.overflow = isCartOpen ? 'hidden' : '';
        };
        document.getElementById('cart-button')?.addEventListener('click', e => { e.preventDefault(); toggleCart(); });
        document.getElementById('cart-close')?.addEventListener('click', toggleCart);
        cartOverlay.addEventListener('click', toggleCart);
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && cartSidebar.classList.contains('open')) {
                toggleCart();
            }
        });

        cartSidebar.addEventListener('submit', async event => {
            const form = event.target.closest('[data-cart-remove-form]');
            if (!form || form.dataset.submitting === 'true') return;

            event.preventDefault();

            // Después de una eliminación AJAX puede quedar una sola fila. En ese
            // caso conservamos la regla existente y abrimos el modal de abandono.
            if (cartSidebar.querySelectorAll('[data-cart-item]').length === 1) {
                const modal = document.getElementById('abandonCartFeedbackModal');
                if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
                return;
            }

            form.dataset.submitting = 'true';

            const button = form.querySelector('button[type="submit"]');
            if (button) button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error('Não foi possível remover o produto.');
                }

                form.closest('[data-cart-item]')?.remove();

                const itemsCount = cartSidebar.querySelector('[data-cart-items-count]');
                const subtotal = cartSidebar.querySelector('.cart-subtotal-value');
                const badge = document.querySelector('#cart-button .cart-badge');

                if (itemsCount) itemsCount.textContent = data.items_text;
                if (subtotal) subtotal.textContent = data.subtotal_formatted;
                if (badge) badge.textContent = data.item_count;
            } catch (error) {
                alert('Não foi possível remover o produto. Tente novamente.');
                form.dataset.submitting = 'false';
                if (button) button.disabled = false;
            }
        });
    }

    // Notifications Sidebar (admin)
    if (notificationsDrawer && notificationsOverlay && notificationsButton) {
        const closeNotifications = () => {
            notificationsDrawer.classList.remove('open');
            notificationsOverlay.classList.remove('open');
            notificationsDrawer.setAttribute('aria-hidden', 'true');
            notificationsButton.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
            notificationsButton.focus();
        };

        const openNotifications = () => {
            cartSidebar?.classList.remove('open');
            cartOverlay?.classList.remove('open');
            notificationsDrawer.classList.add('open');
            notificationsOverlay.classList.add('open');
            notificationsDrawer.setAttribute('aria-hidden', 'false');
            notificationsButton.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            notificationsClose?.focus();
        };

        notificationsButton.addEventListener('click', openNotifications);
        notificationsClose?.addEventListener('click', closeNotifications);
        notificationsOverlay.addEventListener('click', closeNotifications);

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && notificationsDrawer.classList.contains('open')) {
                closeNotifications();
            }
        });

        const notificationsFilter = document.getElementById('adminNotificationsFilter');
        const filteredEmpty = document.getElementById('adminNotificationsFilteredEmpty');

        notificationsFilter?.addEventListener('change', function () {
            let visibleItems = 0;

            notificationsDrawer.querySelectorAll('[data-notification-item]').forEach(function (item) {
                const visible = notificationsFilter.value === 'all'
                    || item.dataset.notificationCategory === notificationsFilter.value;
                item.classList.toggle('d-none', !visible);
                if (visible) visibleItems++;
            });

            filteredEmpty?.classList.toggle('d-none', visibleItems > 0);
        });

        // Marking notifications should not reload the current page. Individual
        // notifications still navigate after their read state is persisted.
        const notificationMenu = notificationsDrawer.closest('.sax-admin-notifications');
        const refreshNotificationState = () => {
            const unreadItems = notificationsDrawer.querySelectorAll(
                '[data-notification-item] .sax-admin-notifications__item.is-unread'
            );
            const operationalAlerts = notificationsDrawer.querySelectorAll('[data-operational-alert]');
            const unreadCount = unreadItems.length + operationalAlerts.length;
            const unreadLabel = notificationsDrawer.querySelector('[data-notifications-unread-count]');
            const readAllForm = notificationsDrawer.querySelector('[data-notifications-read-all]');
            const badge = notificationMenu?.querySelector('[data-notifications-badge]');

            if (unreadLabel) {
                const unreadText = unreadCount === 1
                    ? notificationsDrawer.dataset.notificationsUnreadSingular
                    : notificationsDrawer.dataset.notificationsUnreadPlural;
                unreadLabel.textContent = `${unreadCount} ${unreadText || ''}`.trim();
                unreadLabel.classList.toggle('d-none', unreadCount === 0);
            }

            readAllForm?.classList.toggle('d-none', unreadItems.length === 0);

            if (badge) {
                badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
                badge.classList.toggle('d-none', unreadCount === 0);
            }
        };

        const markItemAsReadInView = form => {
            const item = form.querySelector('.sax-admin-notifications__item');
            if (!item) return;

            item.classList.remove('is-unread');
            form.querySelector('.sax-admin-notifications__dot')?.remove();
            form.querySelector('[data-notification-mark-read]')?.remove();
            refreshNotificationState();
        };

        const submitNotificationAsync = async (form, { navigate = false, trigger = null } = {}) => {
            const button = trigger || form.querySelector('button[type="submit"]');
            if (form.dataset.submitting === 'true') return;

            form.dataset.submitting = 'true';
            if (button) button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) throw new Error(`Notification request failed: ${response.status}`);

                const data = await response.json();

                if (form.dataset.notificationsReadAll !== undefined) {
                    notificationsDrawer.querySelectorAll(
                        '[data-notification-item] .sax-admin-notifications__item.is-unread'
                    ).forEach(item => {
                        item.classList.remove('is-unread');
                        item.closest('[data-notification-item]')
                            ?.querySelector('.sax-admin-notifications__dot')?.remove();
                        item.closest('[data-notification-item]')
                            ?.querySelector('[data-notification-mark-read]')?.remove();
                    });
                    refreshNotificationState();
                } else {
                    markItemAsReadInView(form);
                }

                if (navigate && data.destination) {
                    window.location.assign(data.destination);
                }
            } catch (error) {
                // Keep the original form flow as a safe fallback if JavaScript,
                // the network, or the JSON response is unavailable.
                form.dataset.submitting = 'false';
                if (button) button.disabled = false;
                HTMLFormElement.prototype.submit.call(form);
            }
        };

        notificationsDrawer.querySelectorAll('form[data-notification-item]').forEach(form => {
            form.addEventListener('submit', event => {
                event.preventDefault();
                submitNotificationAsync(form, { navigate: true });
            });
        });

        notificationsDrawer.querySelectorAll('form[data-notifications-read-all]').forEach(form => {
            form.addEventListener('submit', event => {
                event.preventDefault();
                submitNotificationAsync(form);
            });
        });

        notificationsDrawer.querySelectorAll('[data-notification-mark-read]').forEach(button => {
            button.addEventListener('click', event => {
                event.preventDefault();
                const form = button.closest('form[data-notification-item]');
                if (form) submitNotificationAsync(form, { trigger: button });
            });
        });
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
    const loginEmail                     = document.getElementById('login_email');
    const loginPassword                  = document.getElementById('login_password');
    const loginError                     = document.getElementById('loginError');
    const registerName                   = document.getElementById('name');
    const registerDocument               = document.getElementById('register_document');
    const registerDocumentType           = registerForm?.querySelector('select[name="document_type"]');
    const registerPhoneCountry           = registerForm?.querySelector('select[name="phone_country"]');
    const registerPhoneNumber            = document.getElementById('register_phone_number');
    const registerEmail                  = document.getElementById('register_email');
    const registerPassword               = document.getElementById('register_password');
    const registerPasswordConfirmation   = document.getElementById('password_confirmation');
    const registerEmailError             = document.getElementById('registerEmailError');
    const registerPasswordError          = document.getElementById('registerPasswordError');
    const registerError                  = document.getElementById('registerError');
    const forgotEmail                    = document.getElementById('forgot_email');
    const resetPassword                  = document.getElementById('reset_password');
    const resetPasswordConfirmation      = document.getElementById('reset_password_confirmation');
    const resetMessage                   = document.getElementById('resetMessage');

    const isCompleteEmail = email => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    const isStrongPassword = password => /^(?=.*[A-Za-z])(?=.*\d).{8,72}$/.test(password);
    const isValidDocument = (value, type) => window.SaxCustomerDocument?.isValid
        ? window.SaxCustomerDocument.isValid(value, type)
        : /^[A-Za-z0-9./\-\s]{5,30}$/.test(value);
    const isValidPhone = value => /^[0-9\s()+\-]{7,20}$/.test(value);

    const firstErrorMessage = errors => {
        if (!errors || typeof errors !== 'object') return '';
        const firstField = Object.keys(errors)[0];
        const firstValue = firstField ? errors[firstField] : null;
        return Array.isArray(firstValue) ? firstValue[0] : '';
    };

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

    const showMessage = (messageEl, message, type = 'danger') => {
        if (!messageEl) return;
        messageEl.textContent = message || '';
        messageEl.style.display = message ? 'block' : 'none';
        messageEl.className = `small mb-3 text-${type}`;
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

    loginEmail?.addEventListener('input', () => {
        if (!loginEmail.value || isCompleteEmail(loginEmail.value.trim())) {
            loginEmail.classList.remove('is-invalid');
            showMessage(loginError, '');
        }
    });

    loginPassword?.addEventListener('input', () => {
        if (loginPassword.value) {
            loginPassword.classList.remove('is-invalid');
        }
    });

    registerEmail?.addEventListener('input', function () {
        if (!this.value || isCompleteEmail(this.value.trim())) showFieldError(registerEmail, registerEmailError, '');
    });

    registerPasswordConfirmation?.addEventListener('input', function () {
        if (!this.value || this.value === registerPassword?.value) showFieldError(registerPasswordConfirmation, registerPasswordError, '');
    });

    const validateRegisterForm = () => {
        showMessage(registerError, '');

        const nameValue     = registerName?.value.trim() || '';
        const docValue      = registerDocument?.value.trim() || '';
        const docTypeValue  = registerDocumentType?.value || '';
        const countryValue  = registerPhoneCountry?.value || '';
        const phoneValue    = registerPhoneNumber?.value.trim() || '';
        const email        = registerEmail?.value.trim() || '';
        const password     = registerPassword?.value || '';
        const confirmation = registerPasswordConfirmation?.value || '';
        let hasError = false;

        if (!nameValue || nameValue.length < 2) {
            registerName?.classList.add('is-invalid');
            hasError = true;
        }

        if (!docValue || !isValidDocument(docValue, docTypeValue)) {
            registerDocument?.classList.add('is-invalid');
            registerDocumentType?.classList.add('is-invalid');
            hasError = true;
        }

        if (!/^\d{1,6}$/.test(countryValue)) {
            registerPhoneCountry?.classList.add('is-invalid');
            hasError = true;
        }

        if (!phoneValue || !isValidPhone(phoneValue)) {
            registerPhoneNumber?.classList.add('is-invalid');
            hasError = true;
        }

        if (!isCompleteEmail(email)) {
            showFieldError(registerEmail, registerEmailError, 'Informe um e-mail completo, como nome@dominio.com.');
            hasError = true;
        }

        if (!isStrongPassword(password)) {
            showFieldError(registerPassword, registerPasswordError, 'Use no minimo 8 caracteres com pelo menos 1 letra e 1 numero.');
            hasError = true;
        }

        if (password && confirmation && password !== confirmation) {
            showFieldError(registerPasswordConfirmation, registerPasswordError, 'A confirmacao da senha nao confere.');
            hasError = true;
        }

        if (hasError) {
            const firstInvalid = registerForm.querySelector('.is-invalid');
            firstInvalid?.focus();
        }

        return !hasError;
    };

    forgotForm?.addEventListener('submit', function (e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            this.reportValidity();
            return;
        }

        if (!isCompleteEmail((forgotEmail?.value || '').trim())) {
            e.preventDefault();
            forgotEmail?.classList.add('is-invalid');
            showMessage(document.getElementById('forgotMessage'), 'Informe um e-mail valido.', 'danger');
            forgotEmail?.focus();
        }
    });

    resetForm?.addEventListener('submit', function (e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            this.reportValidity();
            return;
        }

        const pass = resetPassword?.value || '';
        const confirmation = resetPasswordConfirmation?.value || '';

        if (!isStrongPassword(pass)) {
            e.preventDefault();
            resetPassword?.classList.add('is-invalid');
            showMessage(resetMessage, 'Use no minimo 8 caracteres com pelo menos 1 letra e 1 numero.', 'danger');
            resetPassword?.focus();
            return;
        }

        if (pass !== confirmation) {
            e.preventDefault();
            resetPasswordConfirmation?.classList.add('is-invalid');
            showMessage(resetMessage, 'A confirmacao da senha nao confere.', 'danger');
            resetPasswordConfirmation?.focus();
        }
    });

    const ajaxForm = (form, { messageEl, btnEl, validate, onSuccess }) => {
        form?.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            if (typeof validate === 'function' && !validate()) {
                return;
            }

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
            .then(async res => {
                let data = null;

                try {
                    data = await res.json();
                } catch (_e) {
                    data = null;
                }

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

                return { status: res.status, ok: res.ok, data };
            })
            .then(payload => {
                if (payload) onSuccess(payload.data || {}, msg, btn, payload.status, payload.ok);
            })
            .catch(() => {
                if (msg) { msg.textContent = 'Erro inesperado. Tente novamente.'; msg.style.display = 'block'; msg.className = 'small mb-3 text-danger'; }
                if (btn) btn.disabled = false;
            });
        });
    };

    ajaxForm(loginForm, {
        messageEl: 'loginError',
        btnEl: null,
        onSuccess(data, msg, _btn, status, ok) {
            const submitBtn = loginForm.querySelector('[type="submit"]');
            if (ok && data.success) {
                window.location.href = data.redirect || '/';
            } else {
                const firstError = firstErrorMessage(data.errors);
                const fallbackMsg = status === 422
                    ? 'Verifique os dados informados e tente novamente.'
                    : (window.saxLang?.dados_incorretos || 'Dados incorretos.');

                if (msg) {
                    msg.textContent = data.message || firstError || fallbackMsg;
                    msg.style.display = 'block';
                    msg.className = 'small mb-3 text-danger';
                }

                if (status === 422 && data.errors?.email) {
                    loginEmail?.classList.add('is-invalid');
                }

                if (status === 422 && data.errors?.password) {
                    loginPassword?.classList.add('is-invalid');
                }

                if (submitBtn) submitBtn.disabled = false;
            }
        },
    });

    ajaxForm(registerForm, {
        messageEl: 'registerError',
        btnEl: 'btnRegister',
        validate: validateRegisterForm,
        onSuccess(data, msg, btn, _status, ok) {
            if (ok && data.success) {
                window.location.href = data.redirect || '/';
                return;
            }

            const errors = data.errors || {};

            if (errors.name) registerName?.classList.add('is-invalid');
            if (errors.document) registerDocument?.classList.add('is-invalid');
            if (errors.document_type) registerDocumentType?.classList.add('is-invalid');
            if (errors.phone_country) registerPhoneCountry?.classList.add('is-invalid');
            if (errors.phone_number) registerPhoneNumber?.classList.add('is-invalid');
            if (errors.email) showFieldError(registerEmail, registerEmailError, errors.email[0]);
            if (errors.password) showFieldError(registerPassword, registerPasswordError, errors.password[0]);

            if (msg) {
                msg.textContent   = data.message || firstErrorMessage(errors) || 'Nao foi possivel concluir o cadastro agora.';
                msg.style.display = 'block';
                msg.className     = 'small mb-3 text-danger';
            }

            if (btn) btn.disabled = false;
        },
    });

    ajaxForm(forgotForm, {
        messageEl: 'forgotMessage',
        btnEl: 'btnForgot',
        onSuccess(data, msg, btn, _status, ok) {
            if (msg) {
                msg.textContent   = data.message || (ok ? 'Operacao realizada com sucesso.' : 'Nao foi possivel concluir a solicitacao.');
                msg.style.display = 'block';
                msg.className     = `small mb-3 ${ok && data.success ? 'text-success' : 'text-danger'}`;
            }
            if (!data.success && btn) btn.disabled = false;
        },
    });

    ajaxForm(resetForm, {
        messageEl: 'resetMessage',
        btnEl: 'btnReset',
        onSuccess(data, msg, btn, _status, ok) {
            if (msg) {
                msg.textContent   = data.message || (ok ? 'Senha atualizada com sucesso.' : 'Nao foi possivel atualizar sua senha.');
                msg.style.display = 'block';
                msg.className     = `small mb-3 ${ok && data.success ? 'text-success' : 'text-danger'}`;
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
// Confirmação global de favoritos: atende cards, vitrines e página do produto.
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('favoriteConfirmationModal');
    const confirmation = window.saxFavoriteConfirmation;

    if (!modalElement || !confirmation || typeof bootstrap === 'undefined') {
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const title = document.getElementById('favoriteConfirmationTitle');
    const message = document.getElementById('favoriteConfirmationMessage');
    const confirmButton = document.getElementById('favoriteConfirmationSubmit');
    let pendingForm = null;

    document.addEventListener('click', function (event) {
        if (event.target.closest('.js-favorite-confirm-form')) {
            event.stopPropagation();
        }
    });

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('.js-favorite-confirm-form');
        if (!form) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        pendingForm = form;

        const isFavorited = form.dataset.isFavorited === '1';
        title.textContent = isFavorited ? confirmation.removeTitle : confirmation.addTitle;
        message.textContent = isFavorited ? confirmation.removeMessage : confirmation.addMessage;
        confirmButton.textContent = isFavorited ? confirmation.removeButton : confirmation.addButton;
        modal.show();
    });

    confirmButton.addEventListener('click', function () {
        if (!pendingForm) {
            return;
        }

        confirmButton.disabled = true;
        pendingForm.submit();
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        pendingForm = null;
        confirmButton.disabled = false;
    });
});
