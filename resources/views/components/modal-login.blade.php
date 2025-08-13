<!-- Modal de Login/Register/Forgot/Reset -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <x-guest-layout>
          <div class="login-container">

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
              @csrf
              <div class="form-group">
                <x-text-input id="login_email" type="email" name="email" :value="old('email')" placeholder="Email" required autofocus autocomplete="username" />
              </div>
              <div class="form-group">
                <x-text-input id="login_password" type="password" name="password" placeholder="Password" required autocomplete="current-password" />
              </div>

              <div id="loginError" class="text-danger mb-2" style="display:none;"></div>

              <button type="submit" class="submit-btn">Login</button>

              <div class="mt-3">
                <p>
                  Não tem uma conta? <a href="#" id="showRegister">Registre-se</a><br>
                  <a href="#" id="showForgot">Esqueci minha senha</a>
                </p>
              </div>
            </form>

            <!-- Register Form -->
            <form method="POST" action="{{ route('register') }}" class="login-form d-none" id="registerForm">
              @csrf
              <div class="form-group">
                <x-text-input id="name" type="text" name="name" :value="old('name')" placeholder="Nome" required autofocus autocomplete="name" />
              </div>
              <div class="form-group">
                <x-text-input id="register_email" type="email" name="email" :value="old('email')" placeholder="Email" required autocomplete="username" />
              </div>
              <div class="form-group">
                <x-text-input id="register_password" type="password" name="password" placeholder="Password" required autocomplete="new-password" />
              </div>
              <div class="form-group">
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirmar senha" required autocomplete="new-password" />
              </div>

              <button type="submit" class="submit-btn">Registre-se</button>

              <div class="mt-3">
                <p>Já tem uma conta? <a href="#" id="showLogin">Faça login aqui</a></p>
              </div>
            </form>

            <!-- Forgot Password Form -->
            <form method="POST" action="{{ route('password.email') }}" class="login-form d-none" id="forgotForm">
              @csrf
              <div class="form-group">
                <x-text-input id="forgot_email" type="email" name="email" placeholder="Seu email" required autocomplete="email" />
              </div>

              <button type="submit" class="submit-btn">Enviar link para resetar senha</button>

              <div class="mt-3">
                <p>
                  Lembrou sua senha? <a href="#" id="showLoginFromForgot">Login</a><br>
                  Não tem conta? <a href="#" id="showRegisterFromForgot">Registre-se</a>
                </p>
              </div>
            </form>

            <!-- Reset Password Form -->
            <form method="POST" action="{{ route('password.update') }}" class="login-form d-none" id="resetForm">
              @csrf
              <input type="hidden" name="token" id="reset_token" value="{{ request()->route('token') }}">

              <div class="form-group">
                <x-text-input id="reset_email" type="email" name="email" :value="old('email', request()->email)" placeholder="Seu email" required autocomplete="email" />
              </div>
              <div class="form-group">
                <x-text-input id="reset_password" type="password" name="password" placeholder="Nova senha" required autocomplete="new-password" />
              </div>
              <div class="form-group">
                <x-text-input id="reset_password_confirmation" type="password" name="password_confirmation" placeholder="Confirmar nova senha" required autocomplete="new-password" />
              </div>

              <!-- <button type="submit" class="submit-btn">Redefinir senha</button> -->

              <div class="mt-3">
                <p>
                  Já tem uma conta? <a href="#" id="showLoginFromReset">Login</a><br>
                  Não tem conta? <a href="#" id="showRegisterFromReset">Registre-se</a>
                </p>
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

    // Ajusta título
    switch(form.id) {
      case 'loginForm': modalTitle.textContent = 'Login'; break;
      case 'registerForm': modalTitle.textContent = 'Registrar'; break;
      case 'forgotForm': modalTitle.textContent = 'Esqueci minha senha'; break;
      case 'resetForm': modalTitle.textContent = 'Redefinir senha'; break;
    }
  }

  // Mostrar Register
  document.getElementById('showRegister').addEventListener('click', e => {
    e.preventDefault();
    showForm(registerForm);
  });
  document.getElementById('showRegisterFromForgot').addEventListener('click', e => {
    e.preventDefault();
    showForm(registerForm);
  });
  document.getElementById('showRegisterFromReset').addEventListener('click', e => {
    e.preventDefault();
    showForm(registerForm);
  });

  // Mostrar Login
  document.getElementById('showLogin').addEventListener('click', e => {
    e.preventDefault();
    showForm(loginForm);
  });
  document.getElementById('showLoginFromForgot').addEventListener('click', e => {
    e.preventDefault();
    showForm(loginForm);
  });
  document.getElementById('showLoginFromReset').addEventListener('click', e => {
    e.preventDefault();
    showForm(loginForm);
  });

  // Mostrar Forgot Password
  document.getElementById('showForgot').addEventListener('click', e => {
    e.preventDefault();
    showForm(forgotForm);
  });

  // Caso você queira, pode colocar Ajax para login, registro e esqueceu senha aqui
  // Mas só implemente se quiser trocar a página, senão o padrão funciona normal.

});
</script>
