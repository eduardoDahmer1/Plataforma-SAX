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

            <!-- Remember Me -->
            <div class="form-group">
                <label for="remember_me" class="checkbox-label">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span>{{ __('Remember me') }}</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Login</button>

            <!-- Forgot Password Link -->
            @if (Route::has('password.request'))
                <div class="forgot-password">
                    <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                </div>
            @endif

            <!-- Register Link -->
            <div class="register-link">
                <p>Don't have an account? <a href="{{ route('register') }}">{{ __('Register here') }}</a></p>
            </div>
        </form>
    </div>
</x-guest-layout>
