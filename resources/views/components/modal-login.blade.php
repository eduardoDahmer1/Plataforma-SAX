<div class="modal fade sax-auth-modal" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header sax-auth-modal-header">
        <div class="sax-auth-modal-brand">
          <a href="{{ route('home') }}" class="sax-auth-modal-logo" aria-label="SAX">
            @if (!empty($webpImage))
              <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="SAX" class="sax-auth-modal-logo-img">
            @else
              <span class="modal-title" id="modalTitle">SAX</span>
            @endif
          </a>
          <p class="sax-auth-modal-subtitle">{{ __('messages.entrar') }} / {{ __('messages.cadastrar') }}</p>
        </div>
        <button type="button" class="sax-auth-modal-close" data-bs-dismiss="modal" aria-label="{{ __('messages.fechar') }}">&times;</button>
      </div>

      <div class="sax-auth-tabs" role="tablist" aria-label="{{ __('messages.acesso_sax') }}">
        <button type="button" class="sax-auth-tab is-active" id="showLogin" data-auth-tab="loginForm">
          {{ __('messages.entrar') }}
        </button>
        <button type="button" class="sax-auth-tab" id="showRegister" data-auth-tab="registerForm">
          {{ __('messages.cadastrar') }}
        </button>
      </div>

      <div class="modal-body">
        <div class="login-container">

          <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}" data-auth-redirect-field>

            @if ($errors->has('email') && session('auth_modal') !== 'register')
              <div class="sax-auth-error-summary" role="alert">
                {{ $errors->first('email') }}
              </div>
            @endif

            <div class="sax-auth-field">
              <input id="login_email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}" required autofocus autocomplete="username" maxlength="255" pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$" title="{{ __('messages.titulo_email_valido') }}" class="form-control @error('email') is-invalid @enderror"/>
            </div>
            <div class="sax-auth-field position-relative">
              <input id="login_password" type="password" name="password" placeholder="{{ __('messages.senha') }}" required autocomplete="current-password" minlength="8" maxlength="72" class="form-control @error('password') is-invalid @enderror"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="login_password">
                <i class="fas fa-eye"></i>
              </button>
            </div>
            
            <div class="text-end sax-auth-form-link-row">
               <a href="#" id="showForgot" class="small">{{ __('messages.esqueci_senha') }}</a>
            </div>

            <div id="loginError" class="text-danger mb-3" style="display:none;" aria-live="polite"></div>
            
            <button type="submit" class="btn btn-primary w-100">
              {{ __('messages.entrar') }}
            </button>
          </form>

          <form method="POST" action="{{ route('register') }}" class="login-form d-none" id="registerForm">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ url()->current() }}" data-auth-redirect-field>

            @if (session('auth_modal') === 'register' && $errors->any())
              <div class="sax-auth-error-summary" role="alert">
                {{ $errors->first() }}
              </div>
            @endif

            <div class="sax-auth-section-label">{{ __('messages.dados_da_conta') }}</div>
            <div class="sax-auth-field">
              <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('messages.nome_completo') }}" required minlength="2" maxlength="255" class="form-control @error('name') is-invalid @enderror"/>
              @error('name')
                <div class="sax-auth-field-error">{{ $message }}</div>
              @enderror
            </div>
            <div class="sax-auth-field">
              <input id="register_email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('messages.email') }}" required autocomplete="email" maxlength="255" pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$" title="{{ __('messages.titulo_email_completo') }}" class="form-control @error('email') is-invalid @enderror"/>
              <div id="registerEmailError" class="sax-auth-field-error" style="display:none;"></div>
              @error('email')
                <div class="sax-auth-field-error">{{ $message }}</div>
              @enderror
            </div>

            <div class="sax-auth-section-label">{{ __('messages.dados_para_compra') }}</div>
            <div class="sax-auth-field">
              <input id="register_document" type="text" name="document" value="{{ old('document') }}" placeholder="{{ __('messages.documento_ruc_ci_cpf') }}" required minlength="5" maxlength="30" pattern="^[A-Za-z0-9./\-\s]{5,30}$" title="{{ __('messages.titulo_documento_valido') }}" class="form-control @error('document') is-invalid @enderror"/>
              @error('document')
                <div class="sax-auth-field-error">{{ $message }}</div>
              @enderror
            </div>
            <div class="sax-auth-field">
              <div class="sax-auth-phone-row">
                <select name="phone_country" class="form-select sax-auth-phone-country @error('phone_country') is-invalid @enderror" required>
                  <option value="595" {{ old('phone_country', '595') == '595' ? 'selected' : '' }}>PRY (+595)</option>
                  <option value="55" {{ old('phone_country') == '55' ? 'selected' : '' }}>BRA (+55)</option>
                </select>

                <input
                  id="register_phone_number"
                  type="text"
                  name="phone_number"
                  value="{{ old('phone_number') }}"
                  placeholder="{{ __('messages.telefone') }}"
                  required
                  inputmode="tel"
                  minlength="7"
                  maxlength="20"
                  pattern="^[0-9\s()+\-]{7,20}$"
                  title="{{ __('messages.titulo_telefone_valido') }}"
                  class="form-control sax-auth-phone-number @error('phone_number') is-invalid @enderror"
                />
              </div>
              @error('phone_country')
                <div class="sax-auth-field-error">{{ $message }}</div>
              @enderror
              @error('phone_number')
                <div class="sax-auth-field-error">{{ $message }}</div>
              @enderror
            </div>


            <div class="sax-auth-section-label">{{ __('messages.senha_de_acesso') }}</div>
            <div class="sax-auth-field position-relative">
              <input id="register_password" type="password" name="password" placeholder="{{ __('messages.senha') }}" required minlength="8" maxlength="72" pattern="^(?=.*[A-Za-z])(?=.*\d).+$" title="{{ __('messages.titulo_senha_regra') }}" class="form-control @error('password') is-invalid @enderror"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="register_password">
                <i class="fas fa-eye"></i>
              </button>
              @error('password')
                <div class="sax-auth-field-error">{{ $message }}</div>
              @enderror
            </div>

            <div class="sax-auth-field position-relative">
              <input id="password_confirmation" type="password" name="password_confirmation" placeholder="{{ __('messages.confirmar_senha') }}" required minlength="8" maxlength="72" class="form-control"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="password_confirmation">
                <i class="fas fa-eye"></i>
              </button>
              <div id="registerPasswordError" class="sax-auth-field-error" style="display:none;"></div>
            </div>

            <div class="sax-auth-password-tip">
              {{ __('messages.use_minimo_8_caracteres') }}
            </div>

            <div class="text-center sax-auth-form-link-row">
              <button type="button" id="generatePassword" class="btn btn-sm btn-secondary text-decoration-none sax-auth-mini-action">
                <i class="fas fa-random me-1"></i> {{ __('messages.gerar_senha') }}
              </button>
            </div>

            <div id="registerError" class="text-danger mb-3" style="display:none;" aria-live="polite"></div>

            <button type="submit" class="btn btn-success w-100" id="btnRegister">
              {{ __('messages.cadastrar') }}
            </button>
          </form>

          <form method="POST" action="{{ route('password.email') }}" class="login-form d-none" id="forgotForm">
            @csrf
            <div class="sax-auth-forgot-copy">
              {{ __('messages.recuperar_senha') }}
            </div>
            <div class="sax-auth-field">
              <input id="forgot_email" type="email" name="email" placeholder="{{ __('messages.email') }}" required maxlength="255" pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$" title="{{ __('messages.titulo_email_valido') }}" class="form-control"/>
            </div>
            <div id="forgotMessage" class="small mb-3" style="display:none;" aria-live="polite"></div>
            <button type="submit" class="btn btn-warning w-100" id="btnForgot">
              {{ __('messages.enviar_link') }}
            </button>
            <div class="text-center sax-auth-form-link-row">
              <a href="#" id="showLoginFromForgot">{{ __('messages.voltar_para_o_login') }}</a>
            </div>
          </form>

          <form method="POST" action="{{ route('password.store') }}" class="login-form d-none" id="resetForm">
            @csrf
            <input type="hidden" name="token" id="reset_token">
            <input type="hidden" name="email" id="reset_email">

            <div class="sax-auth-forgot-copy">
              {{ __('messages.nova_senha') }}
            </div>

            <div class="sax-auth-field position-relative">
              <input id="reset_password" type="password" name="password" placeholder="{{ __('messages.nova_senha') }}" required minlength="8" maxlength="72" pattern="^(?=.*[A-Za-z])(?=.*\d).+$" title="{{ __('messages.titulo_senha_regra') }}" class="form-control"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="reset_password">
                <i class="fas fa-eye"></i>
              </button>
            </div>

            <div class="sax-auth-field position-relative">
              <input id="reset_password_confirmation" type="password" name="password_confirmation" placeholder="{{ __('messages.confirmar_senha') }}" required minlength="8" maxlength="72" class="form-control"/>
              <button type="button" class="btn btn-sm btn-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password" data-target="reset_password_confirmation">
                <i class="fas fa-eye"></i>
              </button>
            </div>

            <div id="resetMessage" class="small mb-3" style="display:none;"></div>

            <button type="submit" class="btn btn-primary w-100" id="btnReset">
              {{ __('messages.atualizar_senha') }}
            </button>
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
    window.saxLang.recuperar_senha  = "{{ __('messages.recuperar_senha') }}";
    window.saxLang.dados_incorretos = "{{ __('messages.dados_incorretos') }}";
    window.saxAuthModalForm        = @json(session('auth_modal'));
</script>
