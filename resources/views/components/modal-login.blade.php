<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header text-white">
        <h5 class="modal-title" id="modalTitle"><i class="fas fa-sign-in-alt me-2"></i>{{ __('messages.entrar') }}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body p-4">
        <div class="login-container">

          <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
            @csrf
            <div class="mb-3">
              <input id="login_email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}" required autofocus class="form-control"/>
            </div>
            <div class="mb-3 position-relative">
              <input id="login_password" type="password" name="password" placeholder="{{ __('messages.senha') }}" required class="form-control"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="login_password">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            
            <div class="text-end mb-3">
               <a href="#" id="showForgot" class="small">{{ __('messages.esqueci_senha') }}</a>
            </div>

            <div id="loginError" class="text-danger mb-3" style="display:none;"></div>
            
            <button type="submit" class="btn btn-primary w-100 mb-4">
              <i class="fas fa-sign-in-alt me-2"></i> {{ __('messages.entrar') }}
            </button>
            
            <div class="text-center small text-muted">
              {{ __('messages.nao_tem_conta') }} <a href="#" id="showRegister">{{ __('messages.registre_se') }}</a>
            </div>
          </form>

          <form method="POST" action="{{ route('register') }}" class="login-form d-none" id="registerForm">
            @csrf
            <div class="mb-3">
              <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('messages.nome_completo') }}" required class="form-control"/>
            </div>
            <div class="mb-3">
              <input id="register_email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}" required class="form-control"/>
            </div>

            <div class="mb-3 position-relative">
              <input id="register_password" type="password" name="password" placeholder="{{ __('messages.senha') }}" required class="form-control"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="register_password">
                <i class="fas fa-eye"></i>
              </button>
            </div>

            <div class="mb-3 position-relative">
              <input id="password_confirmation" type="password" name="password_confirmation" placeholder="{{ __('messages.confirmar_senha') }}" required class="form-control"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="password_confirmation">
                <i class="fas fa-eye"></i>
              </button>
            </div>

            <div class="mb-3 text-center">
              <button type="button" id="generatePassword" class="btn btn-sm btn-secondary text-decoration-none">
                <i class="fas fa-random me-1"></i> {{ __('messages.gerar_senha') }}
              </button>
            </div>

            <button type="submit" class="btn btn-success w-100 mb-4">
              <i class="fas fa-user-plus me-2"></i> {{ __('messages.cadastrar') }}
            </button>

            <div class="text-center small text-muted">
              {{ __('messages.ja_tem_conta') }} <a href="#" id="showLogin">{{ __('messages.fazer_login') }}</a>
            </div>
          </form>

          <form method="POST" action="{{ route('password.email') }}" class="login-form d-none" id="forgotForm">
            @csrf
            <div class="mb-3">
              <input id="forgot_email" type="email" name="email" placeholder="{{ __('messages.email') }}" required class="form-control"/>
            </div>
            <div id="forgotMessage" class="small mb-3" style="display:none;"></div> <button type="submit" class="btn btn-warning w-100 mb-4" id="btnForgot">
              <i class="fas fa-envelope me-2"></i> {{ __('messages.enviar_link') }}
            </button>
            <div class="text-center small">
              <a href="#" id="showLoginFromForgot">{{ __('messages.voltar_para_o_login') }}</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

{{-- Traducciones para JS --}}
<script>
    window.saxLang = window.saxLang || {};
    window.saxLang.entrar          = "{{ __('messages.entrar') }}";
    window.saxLang.cadastrar       = "{{ __('messages.cadastrar') }}";
    window.saxLang.recuperar_senha = "{{ __('messages.recuperar_senha') }}";
</script>