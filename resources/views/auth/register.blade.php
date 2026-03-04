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

                    <div class="form-group">
                        <input id="name" class="form-input" type="text" name="name" :value="old('name')" placeholder="Nome Completo" required autofocus autocomplete="name" />
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
<style>
    /* Container de Fundo */
.auth-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f4f4f4;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    padding: 20px;
}

/* Card Principal */
.auth-card {
    width: 100%;
    max-width: 480px;
    background-color: #ffffff;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

/* Header (Cinza Escuro SAX) */
.auth-header {
    background-color: #777777;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #ffffff;
}

.auth-header-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 18px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Corpo do Formulário */
.auth-body {
    padding: 40px 35px;
}

.form-group {
    margin-bottom: 20px;
}

.form-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #d1d5db;
    border-radius: 2px;
    font-size: 15px;
    color: #333;
    outline: none;
    box-sizing: border-box; /* Garante que o padding não quebre a largura */
}

.form-input:focus {
    border-color: #1a2a6c; /* Azul sutil no foco */
}

/* Botão (Cinza Médio SAX) */
.btn-submit {
    width: 100%;
    background-color: #808080;
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
    margin-top: 10px;
}

.btn-submit:hover {
    background-color: #666666;
}

/* Rodapé de links */
.auth-footer {
    text-align: center;
    margin-top: 25px;
    color: #666;
    font-size: 14px;
}

.link-bold-dark {
    color: #000;
    text-decoration: underline;
    font-weight: 700;
}
</style>