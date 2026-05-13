<x-guest-layout>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-header-title">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"></path>
                    </svg>
                    REGISTRE-SE
                </div>
                <div style="cursor: pointer; font-size: 24px;">&times;</div>
            </div>

            <div class="auth-body">
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="auth-section-label">Dados da conta</div>

                    <div class="form-group">
                        <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}" placeholder="Nome Completo" required autofocus autocomplete="name" />
                    </div>

                    <div class="form-group">
                        <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="username" />
                    </div>

                    <div class="auth-section-label">Dados para compra</div>

                    <div class="form-group">
                        <input id="document" class="form-input" type="text" name="document" value="{{ old('document') }}" placeholder="Documento" required autocomplete="off" />
                    </div>

                    <div class="form-group">
                        <input id="phone_number" class="form-input" type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="Telefone" required autocomplete="tel" />
                    </div>

                    <div class="auth-section-label">Senha de acesso</div>

                    <div class="form-group">
                        <input id="password" class="form-input" type="password" name="password" placeholder="Senha" required autocomplete="new-password" />
                    </div>

                    <div class="form-group">
                        <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" placeholder="Confirmar Senha" required autocomplete="new-password" />
                    </div>

                    <button type="submit" class="btn-submit">
                        REGISTRAR
                    </button>

                    <div class="auth-footer">
                        Já possui uma conta? 
                        <a href="{{ route('login') }}" class="link-bold-dark">
                            Faça login aqui
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>