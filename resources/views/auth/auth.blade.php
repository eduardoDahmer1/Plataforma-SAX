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
                <div class="auth-header-close">&times;</div>
            </div>

            <div class="auth-body">
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="form-group">
                        <input id="name" class="form-input" type="text" name="name" :value="old('name')" placeholder="Nome" required autofocus autocomplete="name" />
                    </div>

                    <div class="form-group">
                        <input id="email" class="form-input" type="email" name="email" :value="old('email')" placeholder="Email" required autocomplete="username" />
                    </div>

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
                        Já tem uma conta? 
                        <a href="{{ route('login') }}" class="link-bold-dark">
                            Login aqui
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
<style>
    /* Container que centraliza o formulário na tela */
.auth-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f4f4f4; /* Fundo neutro */
    font-family: sans-serif;
    padding: 20px;
}

/* O "Card" branco */
.auth-card {
    width: 100%;
    max-width: 480px;
    background-color: #ffffff;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

/* Header Cinza Escuro conforme o print */
.auth-header {
    background-color: #777777;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #ffffff;
}

.auth-header-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: bold;
    font-size: 18px;
    text-transform: uppercase;
}

.auth-header-close {
    font-size: 24px;
    cursor: pointer;
    line-height: 1;
}

/* Corpo e Inputs */
.auth-body {
    padding: 40px 35px;
}

.form-group {
    margin-bottom: 20px;
}

.form-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #cccccc;
    border-radius: 4px;
    font-size: 16px;
    color: #333;
    outline: none;
    transition: border-color 0.2s;
}

.form-input:focus {
    border-color: #1a2a6c; /* Azul discreto ao clicar */
}

/* Botão Cinza Médio */
.btn-submit {
    width: 100%;
    background-color: #808080;
    color: #ffffff;
    padding: 14px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    font-weight: bold;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-submit:hover {
    background-color: #666666;
}

/* Rodapé e Links */
.auth-footer {
    text-align: center;
    margin-top: 25px;
    color: #666;
    font-size: 14px;
}

.link-bold-dark {
    color: #000;
    text-decoration: underline;
    font-weight: bold;
}
</style>