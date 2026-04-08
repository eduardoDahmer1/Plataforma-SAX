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

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <input id="email" class="form-input" type="email" name="email" placeholder="Email" required autofocus />
                    </div>

                    <div class="form-group">
                        <input id="password" class="form-input" type="password" name="password" placeholder="{{ __('messages.senha') }}" required />
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
<style>
    /* Estilização inspirada no Modal SAX */
.login-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f4f4l4; /* Fundo leve para destacar o card */
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

.login-card {
    width: 100%;
    max-width: 480px;
    background-color: #ffffff;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

/* Header Cinza do Print */
.login-header {
    background-color: #777777;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #ffffff;
}

.login-header-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 18px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.login-header-close {
    font-size: 24px;
    cursor: pointer;
    line-height: 1;
    opacity: 0.8;
}

.login-header-close:hover {
    opacity: 1;
}

/* Formulário e Inputs */
.login-body {
    padding: 40px 35px;
}

.form-group {
    margin-bottom: 20px;
    position: relative;
}

.form-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #d1d5db;
    border-radius: 2px;
    font-size: 15px;
    color: #333;
    transition: border-color 0.2s;
    outline: none;
}

.form-input:focus {
    border-color: #1a2a6c; /* Azul sutil no foco */
}

/* Links (Esqueceu a senha / Registro) */
.link-bold-dark {
    color: #000;
    text-decoration: underline;
    font-size: 14px;
    font-weight: 700;
}

.forgot-password-container {
    text-align: right;
    margin-bottom: 25px;
}

/* Botão Entrar */
.btn-submit {
    width: 100%;
    background-color: #808080; /* Cinza médio do print */
    color: #ffffff;
    padding: 14px;
    border: none;
    border-radius: 2px;
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    transition: background-color 0.3s ease;
}

.btn-submit:hover {
    background-color: #666666;
}

.register-footer {
    text-align: center;
    margin-top: 25px;
    color: #666;
    font-size: 14px;
}
</style>