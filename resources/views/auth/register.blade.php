<x-guest-layout>
    <div class="login-container">
        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('register') }}" class="login-form">
            @csrf

            <!-- Name -->
            <div class="form-group">
                <x-text-input id="name" type="text" name="name" :value="old('name')" placeholder="Name" required autofocus autocomplete="name" />
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <x-text-input id="email" type="email" name="email" :value="old('email')" placeholder="Email" required autocomplete="username" />
            </div>

            <!-- Password -->
            <div class="form-group">
                <x-text-input id="password" type="password" name="password" placeholder="Password" required autocomplete="new-password" />
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm Password" required autocomplete="new-password" />
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Register</button>

            <!-- Already Registered Link -->
            <div class="already-registered">
                <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
            </div>
        </form>
    </div>
</x-guest-layout>
