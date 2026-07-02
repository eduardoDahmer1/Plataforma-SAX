<x-guest-layout>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="login-header-title">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z"></path></svg>
                    {{ __('messages.entrar') }}
                </div>
                <div class="login-header-close">&times;</div>
            </div>

            <div class="login-body">
                <x-auth-session-status class="mb-4" :status="session('status')" />

                @if ($errors->any())
                    <div class="sax-auth-error-summary" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <input id="email" class="form-input @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus autocomplete="username" maxlength="255" pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$" title="Informe um e-mail valido, como nome@dominio.com" />
                        @error('email')
                            <div class="sax-auth-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input id="password" class="form-input @error('password') is-invalid @enderror" type="password" name="password" placeholder="{{ __('messages.senha') }}" required autocomplete="current-password" minlength="8" maxlength="72" />
                        @error('password')
                            <div class="sax-auth-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="forgot-password-container">
                        <a href="{{ route('password.request') }}" class="link-bold-dark">
                            {{ __('messages.esqueci_senha') }}
                        </a>
                    </div>

                    <button type="submit" class="btn-submit">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z"></path></svg>
                        {{ __('messages.entrar') }}
                    </button>

                    <div class="register-footer">
                        {{ __('messages.nao_tem_conta') }}
                        <a href="{{ route('register') }}" class="link-bold-dark">{{ __('messages.registre_se') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
