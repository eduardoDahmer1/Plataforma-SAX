<x-guest-layout>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="login-header-title">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                    </svg>
                    VERIFICAR E-MAIL
                </div>
                <div class="login-header-close" onclick="window.location.href='/'">&times;</div>
            </div>

            <div class="login-body">
                <div class="mb-4 text-sm text-gray-600">
                    Obrigado por se cadastrar! Antes de começar, você poderia verificar seu endereço de e-mail clicando no link que acabamos de enviar para você? Se você não recebeu o e-mail, teremos o prazer de enviar outro.
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        Um novo link de verificação foi enviado para o endereço de e-mail fornecido durante o registro.
                    </div>
                @endif

                <div class="mt-8">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn-submit">
                            REENVIAR E-MAIL DE VERIFICAÇÃO
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="register-footer">
                        @csrf
                        <button type="submit" class="link-bold-dark" style="background: none; border: none; cursor: pointer; text-transform: uppercase;">
                            SAIR
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

<style>
/* Estilização baseada no seu padrão Sax */
.login-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f4f4f4;
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

.login-body {
    padding: 40px 35px;
}

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
    transition: background-color 0.3s ease;
}

.btn-submit:hover {
    background-color: #666666;
}

.register-footer {
    text-align: center;
    margin-top: 25px;
}

.link-bold-dark {
    color: #000;
    text-decoration: underline;
    font-size: 14px;
    font-weight: 700;
}
</style>