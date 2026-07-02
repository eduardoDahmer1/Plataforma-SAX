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

                @if ($errors->any())
                    <div class="sax-auth-error-summary" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="auth-section-label">Dados da conta</div>

                    <div class="form-group">
                        <input id="name" class="form-input @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" placeholder="Nome Completo" required autofocus autocomplete="name" minlength="2" maxlength="255" />
                        @error('name')
                            <div class="sax-auth-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input id="email" class="form-input @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="username" maxlength="255" pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$" title="Informe um e-mail valido, como nome@dominio.com" />
                        @error('email')
                            <div class="sax-auth-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-section-label">Dados para compra</div>

                    <div class="form-group">
                        <input id="document" class="form-input @error('document') is-invalid @enderror" type="text" name="document" value="{{ old('document') }}" placeholder="Documento (RUC/CI/CPF)" required autocomplete="off" minlength="5" maxlength="30" pattern="^[A-Za-z0-9./\-\s]{5,30}$" title="Informe um documento valido." />
                        @error('document')
                            <div class="sax-auth-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <select id="phone_country" class="form-input @error('phone_country') is-invalid @enderror" name="phone_country" required>
                            <option value="595" {{ old('phone_country', '595') == '595' ? 'selected' : '' }}>PRY (+595)</option>
                            <option value="55" {{ old('phone_country') == '55' ? 'selected' : '' }}>BRA (+55)</option>
                        </select>
                        @error('phone_country')
                            <div class="sax-auth-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input id="phone_number" class="form-input @error('phone_number') is-invalid @enderror" type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="Telefone" required autocomplete="tel" inputmode="tel" minlength="7" maxlength="20" pattern="^[0-9\s()+\-]{7,20}$" title="Informe um telefone valido, sem letras." />
                        @error('phone_number')
                            <div class="sax-auth-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="auth-section-label">Senha de acesso</div>

                    <div class="form-group">
                        <input id="password" class="form-input @error('password') is-invalid @enderror" type="password" name="password" placeholder="Senha" required autocomplete="new-password" minlength="8" maxlength="72" pattern="^(?=.*[A-Za-z])(?=.*\d).+$" title="Use pelo menos 8 caracteres com 1 letra e 1 numero." />
                        @error('password')
                            <div class="sax-auth-field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" placeholder="Confirmar Senha" required autocomplete="new-password" minlength="8" maxlength="72" />
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