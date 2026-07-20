@extends('layout.dashboard')

@section('content')
<div class="sax-password-wrapper">
    <div class="dashboard-header mb-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
            <div>
                <span class="password-eyebrow">Segurança da conta</span>
                <h2 class="sax-title text-uppercase letter-spacing-2 mb-2">Alterar senha</h2>
                <p class="text-muted mb-0">Escolha uma senha exclusiva para proteger sua conta e seus pedidos.</p>
            </div>
            <a href="{{ route('user.dashboard') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> {{ __('messages.voltar') }}
            </a>
        </div>
        <div class="sax-divider-dark"></div>
    </div>

    @if (session('success'))
        <div class="alert alert-success shadow-sm mb-3" role="alert">{{ session('success') }}</div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning shadow-sm mb-3" role="alert">{{ session('warning') }}</div>
    @endif

    <div class="password-panel card border-0 shadow-sm rounded-4 p-4 p-md-5">
        <div class="password-panel-heading mb-4">
            <div class="password-icon"><i class="fas fa-lock"></i></div>
            <div>
                <h3>Nova senha de acesso</h3>
                <p>Confirme primeiro sua identidade com a senha usada atualmente.</p>
            </div>
        </div>

        <form action="{{ route('user.password.update') }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="current_password" class="sax-label">Senha atual</label>
                <div class="password-input-wrap">
                    <input id="current_password" type="password" name="current_password"
                        class="form-control sax-input @error('current_password') is-invalid @enderror"
                        required autocomplete="current-password" autofocus>
                    <button type="button" class="password-toggle" data-password-toggle="current_password" aria-label="Mostrar senha">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label for="password" class="sax-label">Nova senha</label>
                    <div class="password-input-wrap">
                        <input id="password" type="password" name="password"
                            class="form-control sax-input @error('password') is-invalid @enderror"
                            required minlength="8" maxlength="72" autocomplete="new-password">
                        <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Mostrar senha">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="sax-label">Confirmar nova senha</label>
                    <div class="password-input-wrap">
                        <input id="password_confirmation" type="password" name="password_confirmation"
                            class="form-control sax-input" required minlength="8" maxlength="72" autocomplete="new-password">
                        <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="Mostrar senha">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="password-requirements mt-4">
                <i class="fas fa-shield-alt"></i>
                <span>Use pelo menos 8 caracteres, incluindo uma letra e um número.</span>
            </div>

            <div class="d-flex justify-content-end mt-5">
                <button type="submit" class="btn btn-dark btn-sax-submit px-5 py-3 text-uppercase fw-bold letter-spacing-1">
                    Alterar senha
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .password-eyebrow { display:block; margin-bottom:8px; color:#2970ff; font-size:.68rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; }
    .password-panel { max-width:860px; }
    .password-panel-heading { display:flex; align-items:center; gap:16px; padding-bottom:22px; border-bottom:1px solid #e7e7e7; }
    .password-panel-heading h3 { margin:0 0 4px; font-size:1rem; font-weight:750; text-transform:uppercase; letter-spacing:.06em; }
    .password-panel-heading p { margin:0; color:#7a7a7a; font-size:.86rem; }
    .password-icon { display:grid; flex:0 0 46px; width:46px; height:46px; place-items:center; color:#fff; background:#111; border-radius:50%; }
    .password-input-wrap { position:relative; }
    .password-input-wrap .sax-input { min-height:52px; padding-right:48px; }
    .password-toggle { position:absolute; top:50%; right:14px; padding:6px; color:#777; background:transparent; border:0; transform:translateY(-50%); }
    .password-toggle:hover { color:#111; }
    .password-requirements { display:flex; align-items:center; gap:10px; padding:13px 15px; color:#596579; background:#f5f7fa; border:1px solid #e2e7ee; border-radius:10px; font-size:.8rem; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-password-toggle'));
            if (!input) return;
            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.setAttribute('aria-label', showing ? 'Mostrar senha' : 'Ocultar senha');
            button.querySelector('i')?.classList.toggle('fa-eye', showing);
            button.querySelector('i')?.classList.toggle('fa-eye-slash', !showing);
        });
    });
});
</script>
@endpush
