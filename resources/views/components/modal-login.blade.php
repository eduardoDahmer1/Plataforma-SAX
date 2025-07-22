<!-- Modal de Login/Register -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <x-guest-layout>
                    <div class="login-container">

                        <!-- Login Form -->
                        <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
                            @csrf
                            <div class="form-group">
                                <x-text-input id="login_email" type="email" name="email" :value="old('email')"
                                    placeholder="Email" required autofocus autocomplete="username" />
                            </div>

                            <div class="form-group">
                                <x-text-input id="login_password" type="password" name="password" placeholder="Password"
                                    required autocomplete="current-password" />
                            </div>

                            <button type="submit" class="submit-btn">Login</button>

                            <div class="register-link mt-3">
                                <p>Don't have an account? <a href="#" id="showRegister">Register here</a></p>
                            </div>
                        </form>

                        <!-- Register Form -->
                        <form method="POST" action="{{ route('register') }}" class="login-form d-none"
                            id="registerForm">
                            @csrf
                            <div class="form-group">
                                <x-text-input id="name" type="text" name="name" :value="old('name')" placeholder="Name"
                                    required autofocus autocomplete="name" />
                            </div>

                            <div class="form-group">
                                <x-text-input id="register_email" type="email" name="email" :value="old('email')"
                                    placeholder="Email" required autocomplete="username" />
                            </div>

                            <div class="form-group">
                                <x-text-input id="register_password" type="password" name="password"
                                    placeholder="Password" required autocomplete="new-password" />
                            </div>

                            <div class="form-group">
                                <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                                    placeholder="Confirm Password" required autocomplete="new-password" />
                            </div>

                            <button type="submit" class="submit-btn">Register</button>

                            <div class="already-registered mt-3">
                                <p>Already have an account? <a href="#" id="showLogin">Login here</a></p>
                            </div>
                        </form>

                    </div>
                </x-guest-layout>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const modalTitle = document.getElementById('modalTitle');

    document.getElementById('showRegister').addEventListener('click', function(e) {
        e.preventDefault();
        loginForm.classList.add('d-none');
        registerForm.classList.remove('d-none');
        modalTitle.textContent = 'Register';
    });

    document.getElementById('showLogin').addEventListener('click', function(e) {
        e.preventDefault();
        registerForm.classList.add('d-none');
        loginForm.classList.remove('d-none');
        modalTitle.textContent = 'Login';
    });
});
</script>