<header class="bg-dark text-white p-3">
    <div class="container d-flex justify-content-between align-items-center">
        <h1>Plataforma de documentos e cursos Sax</h1>

        @if (Auth::check())
        <!-- Botão de Logout (aparece se estiver logado) -->
        <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-danger">
                <i class="fa fa-sign-out-alt"></i> Logout
            </button>
        </form>
        @else
        <!-- Botão de Login que abre o Modal -->
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#loginModal">
            <i class="fa fa-sign-in-alt"></i> Login
        </button>
        @endif
    </div>
</header>

<!-- Modal de Login -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <x-guest-layout>
                    <div class="login-container">
                        <!-- Session Status -->
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <form method="POST" action="{{ route('login') }}" class="login-form">
                            @csrf

                            <!-- Email Address -->
                            <div class="form-group">
                                <x-text-input id="email" type="email" name="email" :value="old('email')" placeholder="Email" required autofocus autocomplete="username" />
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <x-text-input id="password" type="password" name="password" placeholder="Password" required autocomplete="current-password" />
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="submit-btn">Login</button>

                            <!-- Forgot Password Link
                            @if (Route::has('password.request'))
                                <div class="forgot-password">
                                    <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                                </div>
                            @endif

                            Register Link 
                            <div class="register-link">
                                <p>Don't have an account? <a href="{{ route('register') }}">{{ __('Register here') }}</a></p>
                            </div>-->
                        </form>
                    </div>
                </x-guest-layout>
            </div>
        </div>
    </div>
</div>
