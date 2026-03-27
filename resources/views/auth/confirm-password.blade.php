<x-guest-layout>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="login-header-title">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                    CONFIRMAR SENHA
                </div>
                <div class="login-header-close" onclick="window.location.href='/'">&times;</div>
            </div>

            <div class="login-body">
                <div class="mb-4 text-sm text-gray-600">
                    Esta é uma área segura. Por favor, confirme sua senha antes de continuar.
                </div>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <div class="form-group">
                        <input id="password" class="form-input" 
                               type="password" 
                               name="password" 
                               placeholder="Sua Senha Atual"
                               required 
                               autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="btn-submit">
                            CONFIRMAR
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

<style>
/* Padronização Sax Department */
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
</style>