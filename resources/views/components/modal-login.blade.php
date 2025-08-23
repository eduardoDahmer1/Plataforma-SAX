<!-- Modal de Login/Register/Forgot/Reset -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow-lg">

      <!-- Header do Modal -->
      <div class="modal-header bg-primary text-white rounded-top">
        <h5 class="modal-title" id="modalTitle"><i class="fas fa-sign-in-alt me-2"></i>Login</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <!-- Corpo do Modal -->
      <div class="modal-body p-4">
        <x-guest-layout>
          <div class="login-container">

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
              @csrf
              <div class="mb-3">
                <x-text-input id="login_email" type="email" name="email" :value="old('email')" placeholder="Email" required autofocus autocomplete="username" class="form-control"/>
              </div>
              <div class="mb-3 position-relative">
                <x-text-input id="login_password" type="password" name="password" placeholder="Senha" required autocomplete="current-password" class="form-control"/>
                <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="login_password">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <div id="loginError" class="text-danger mb-3" style="display:none;"></div>
              <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i> Entrar
              </button>
              <div class="text-center small">
                Não tem uma conta? <a href="#" id="showRegister">Registre-se</a>
              </div>
            </form>

            <!-- Register Form -->
            <form method="POST" action="{{ route('register') }}" class="login-form d-none" id="registerForm">
              @csrf
              <div class="mb-3">
                <x-text-input id="name" type="text" name="name" :value="old('name')" placeholder="Nome" required autofocus autocomplete="name" class="form-control"/>
              </div>
              <div class="mb-3">
                <x-text-input id="register_email" type="email" name="email" :value="old('email')" placeholder="Email" required autocomplete="username" class="form-control"/>
              </div>

              <!-- Senha -->
              <div class="mb-3 position-relative">
                <x-text-input id="register_password" type="password" name="password" placeholder="Senha" required autocomplete="new-password" class="form-control"/>
                <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="register_password">
                  <i class="fas fa-eye"></i>
                </button>
              </div>

              <!-- Confirmação de Senha -->
              <div class="mb-3 position-relative">
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirmar senha" required autocomplete="new-password" class="form-control"/>
                <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="password_confirmation">
                  <i class="fas fa-eye"></i>
                </button>
              </div>

              <!-- Botão Gerar Senha -->
              <div class="mb-3 text-center">
                <button type="button" id="generatePassword" class="btn btn-sm btn-secondary">
                  <i class="fas fa-random me-1"></i> Gerar senha segura
                </button>
              </div>

              <button type="submit" class="btn btn-success w-100 mb-3">
                <i class="fas fa-user-plus me-2"></i> Registrar
              </button>

              <div class="text-center small">
                Já tem uma conta? <a href="#" id="showLogin">Faça login aqui</a>
              </div>
            </form>


            <!-- Forgot Password Form -->
            <form method="POST" action="{{ route('password.email') }}" class="login-form d-none" id="forgotForm">
              @csrf
              <div class="mb-3">
                <x-text-input id="forgot_email" type="email" name="email" placeholder="Seu email" required autocomplete="email" class="form-control"/>
              </div>
              <button type="submit" class="btn btn-warning w-100 mb-3">
                <i class="fas fa-envelope me-2"></i> Enviar link de redefinição
              </button>
              <div class="text-center small">
                Lembrou sua senha? <a href="#" id="showLoginFromForgot">Login</a><br>
                Não tem conta? <a href="#" id="showRegisterFromForgot">Registre-se</a>
              </div>
            </form>

            <!-- Reset Password Form -->
            <form method="POST" action="{{ route('password.update') }}" class="login-form d-none" id="resetForm">
              @csrf
              <input type="hidden" name="token" id="reset_token" value="{{ request()->route('token') }}">
              <div class="mb-3">
                <x-text-input id="reset_email" type="email" name="email" :value="old('email', request()->email)" placeholder="Seu email" required autocomplete="email" class="form-control"/>
              </div>
              <div class="mb-3 position-relative">
                <x-text-input id="reset_password" type="password" name="password" placeholder="Nova senha" required autocomplete="new-password" class="form-control"/>
                <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="reset_password">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <div class="mb-3 position-relative">
                <x-text-input id="reset_password_confirmation" type="password" name="password_confirmation" placeholder="Confirmar nova senha" required autocomplete="new-password" class="form-control"/>
                <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="reset_password_confirmation">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <button type="submit" class="btn btn-info w-100 mb-3">
                <i class="fas fa-key me-2"></i> Redefinir senha
              </button>
              <div class="text-center small">
                Já tem uma conta? <a href="#" id="showLoginFromReset">Login</a><br>
                Não tem conta? <a href="#" id="showRegisterFromReset">Registre-se</a>
              </div>
            </form>

          </div>
        </x-guest-layout>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const forgotForm = document.getElementById('forgotForm');
  const resetForm = document.getElementById('resetForm');
  const modalTitle = document.getElementById('modalTitle');

  function showForm(form) {
    [loginForm, registerForm, forgotForm, resetForm].forEach(f => f.classList.add('d-none'));
    form.classList.remove('d-none');

    switch(form.id) {
      case 'loginForm': modalTitle.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Login'; break;
      case 'registerForm': modalTitle.innerHTML = '<i class="fas fa-user-plus me-2"></i>Registrar'; break;
      case 'forgotForm': modalTitle.innerHTML = '<i class="fas fa-envelope me-2"></i>Esqueci minha senha'; break;
      case 'resetForm': modalTitle.innerHTML = '<i class="fas fa-key me-2"></i>Redefinir senha'; break;
    }
  }

  // Troca de formulários
  document.querySelectorAll('#showRegister, #showRegisterFromForgot, #showRegisterFromReset')
    .forEach(el => el.addEventListener('click', e => { e.preventDefault(); showForm(registerForm); }));
  document.querySelectorAll('#showLogin, #showLoginFromForgot, #showLoginFromReset')
    .forEach(el => el.addEventListener('click', e => { e.preventDefault(); showForm(loginForm); }));
  document.getElementById('showForgot')?.addEventListener('click', e => { e.preventDefault(); showForm(forgotForm); });

  // Gerador de senha
  document.getElementById('generatePassword')?.addEventListener('click', function () {
    const length = Math.floor(Math.random() * 3) + 10; // 10 a 12
    const lowercase = "abcdefghijklmnopqrstuvwxyz";
    const uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const numbers   = "0123456789";
    const symbols   = "!@#$%&*";
    const allChars  = lowercase + uppercase + numbers + symbols;

    let password = "";
    password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
    password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
    password += numbers.charAt(Math.floor(Math.random() * numbers.length));
    password += symbols.charAt(Math.floor(Math.random() * symbols.length));

    for (let i = password.length; i < length; i++) {
      password += allChars.charAt(Math.floor(Math.random() * allChars.length));
    }

    // embaralha
    password = password.split('').sort(() => 0.5 - Math.random()).join('');

    document.getElementById('register_password').value = password;
    document.getElementById('password_confirmation').value = password;
    alert("Senha gerada: " + password);
  });

  // Mostrar/Ocultar senha
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function() {
      const target = document.getElementById(this.dataset.target);
      if(target.type === "password") {
        target.type = "text";
        this.querySelector('i').classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        target.type = "password";
        this.querySelector('i').classList.replace('fa-eye-slash', 'fa-eye');
      }
    });
  });

});
</script>

<style>
.login-container .form-control { padding: 0.75rem; font-size: 0.95rem; border-radius: 0.5rem; }
.login-form .btn { border-radius: 0.5rem; }
.modal-header { font-weight: 600; font-size: 1.1rem; }
.position-relative .btn { font-size: 0.75rem; }
</style>
