<x-guest-layout>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="login-header-title">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2V7a5 5 0 00-5-5zM7 7a3 3 0 116 0v2H7V7z"></path>
                    </svg>
                    REDEFINIR SENHA
                </div>
                <div class="login-header-close" onclick="window.location.href='/'">&times;</div>
            </div>

            <div class="login-body">
                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="form-group">
                        <input id="email" class="form-input" type="email" name="email" value="{{ old('email', $request->email) }}" placeholder="E-mail" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="form-group">
                        <input id="password" class="form-input" type="password" name="password" placeholder="Nova Senha" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="form-group">
                        <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" placeholder="Confirmar Nova Senha" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <button type="submit" class="btn-submit">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        ATUALIZAR SENHA
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

<style>
/* Reutilizando a estilização do seu Modal SAX */
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

.login-header-close {
    font-size: 24px;
    cursor: pointer;
    line-height: 1;
    opacity: 0.8;
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
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    transition: background-color 0.3s ease;
}

.btn-submit:hover {
    background-color: #666666;
}
</style>