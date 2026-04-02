<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header text-white">
        <h5 class="modal-title" id="modalTitle"><i class="fas fa-sign-in-alt me-2"></i>Entrar</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body p-4">
        <div class="login-container">

          <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
            @csrf
            <div class="mb-3">
              <input id="login_email" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus class="form-control"/>
            </div>
            <div class="mb-3 position-relative">
              <input id="login_password" type="password" name="password" placeholder="Senha" required class="form-control"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="login_password">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            
            <div class="text-end mb-3">
               <a href="#" id="showForgot" class="small">Esqueceu a senha?</a>
            </div>

            <div id="loginError" class="text-danger mb-3" style="display:none;"></div>
            
            <button type="submit" class="btn btn-primary w-100 mb-4">
              <i class="fas fa-sign-in-alt me-2"></i> Entrar
            </button>
            
            <div class="text-center small text-muted">
              Não tem uma conta? <a href="#" id="showRegister">Registre-se</a>
            </div>
          </form>

          <form method="POST" action="{{ route('register') }}" class="login-form d-none" id="registerForm">
            @csrf
            <div class="mb-3">
              <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Nome" required class="form-control"/>
            </div>
            <div class="mb-3">
              <input id="register_email" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required class="form-control"/>
            </div>

            <div class="mb-3 position-relative">
              <input id="register_password" type="password" name="password" placeholder="Senha" required class="form-control"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="register_password">
                <i class="fas fa-eye"></i>
              </button>
            </div>

            <div class="mb-3 position-relative">
              <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirmar senha" required class="form-control"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="password_confirmation">
                <i class="fas fa-eye"></i>
              </button>
            </div>

            <div class="mb-3 text-center">
              <button type="button" id="generatePassword" class="btn btn-sm btn-secondary text-decoration-none">
                <i class="fas fa-random me-1"></i> Sugerir senha segura
              </button>
            </div>

            <button type="submit" class="btn btn-success w-100 mb-4">
              <i class="fas fa-user-plus me-2"></i> Registrar
            </button>

            <div class="text-center small text-muted">
              Já tem uma conta? <a href="#" id="showLogin">Faça login aqui</a>
            </div>
          </form>

          <form method="POST" action="{{ route('password.email') }}" class="login-form d-none" id="forgotForm">
            @csrf
            <div class="mb-3">
              <input id="forgot_email" type="email" name="email" placeholder="Seu email cadastrado" required class="form-control"/>
            </div>
            <div id="forgotMessage" class="small mb-3" style="display:none;"></div> <button type="submit" class="btn btn-warning w-100 mb-4" id="btnForgot">
              <i class="fas fa-envelope me-2"></i> Enviar Link
            </button>
            <div class="text-center small">
              <a href="#" id="showLoginFromForgot">Voltar para o Login</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

{{-- JS migrado a app-custom.js --}}