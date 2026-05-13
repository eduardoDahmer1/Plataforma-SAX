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
