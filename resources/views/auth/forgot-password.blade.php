<x-guest-layout>

    <div class="container">
        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form class="login" method="POST" action="{{ route('password.email') }}">
            @csrf
            <fieldset>
                <legend class="legend">Forgot Your Password?</legend>

                <div class="input">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required
                        autofocus />
                    <span><i class="fa fa-envelope-o"></i></span>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="submit" class="submit"><i class="fa fa-long-arrow-right"></i></button>
                </div>
            </fieldset>

            @if(session('status'))
            <div class="feedback">
                {{ session('status') }}
            </div>
            @endif
        </form>
    </div>
</x-guest-layout>

{{-- JS migrado a app-custom.js --}}