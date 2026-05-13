<x-guest-layout>
    <div class="verify-wrapper">
        <div class="verify-card">
            <div class="verify-header">
                <div class="verify-header-title">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"></rect>
                        <path d="M4.5 7.5L12 13L19.5 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    Verificar e-mail
                </div>
                <button type="button" class="verify-close" onclick="window.location.href='/'" aria-label="Fechar">
                    &times;
                </button>
            </div>

            <div class="verify-body">
                <p class="verify-kicker">Conta SAX</p>
                <h1 class="verify-title">Confirme seu e-mail</h1>
                <p class="verify-description">
                    Enviamos um link para confirmar sua conta. Abra o e-mail abaixo e clique no botão de verificação para continuar comprando na SAX.
                </p>

                <div class="verify-email-box">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"></rect>
                        <path d="M4.5 7.5L12 13L19.5 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>{{ auth()->user()->email ?? 'seu e-mail cadastrado' }}</span>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="verify-status">
                        Um novo link de verificação foi enviado para o endereço de e-mail fornecido durante o registro.
                    </div>
                @endif

                <div class="verify-help">
                    Não encontrou? Verifique spam ou solicite um novo link.
                </div>

                <div class="verify-actions">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="verify-submit">
                            REENVIAR E-MAIL DE VERIFICAÇÃO
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="verify-logout-form">
                        @csrf
                        <button type="submit" class="verify-logout">
                            SAIR
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
