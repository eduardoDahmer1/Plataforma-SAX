<div class="step" id="step2">
    <div class="sax-checkout-box">
        <h4 class="sax-step-title">
            <span class="step-number">02</span> {{ __('messages.passo_dados_pessoais') }}
        </h4>

        <p class="sax-step-helper">Revise seus dados de contato para receber as atualizacoes do pedido.</p>

        <div class="row g-4">
            <div class="col-md-6 sax-input-group">
                <label>{{ __('messages.nome_completo') }} *</label>
                <input type="text" name="name" class="sax-form-control"
                    value="{{ old('name') ?? auth()->user()->name }}" autocomplete="name" required>
            </div>

            <div class="col-md-6 sax-input-group">
                <label>{{ __('messages.documento_identidade') }} *</label>
                <input type="text" name="document" class="sax-form-control"
                    value="{{ old('document') ?? auth()->user()->document }}" required>
            </div>

            <div class="col-md-6 sax-input-group">
                <label>{{ __('messages.email') }} *</label>
                <input type="email" name="email" class="sax-form-control"
                    value="{{ old('email') ?? auth()->user()->email }}" autocomplete="email" required>
            </div>

            <div class="col-md-6 sax-input-group">
                <label>{{ __('messages.telefone') }} *</label>

                <div class="sax-auth-phone-row d-flex sax-checkout-phone-row">
                    <select name="phone_country"
                        class="sax-auth-phone-country @error('phone_country') is-invalid @enderror" 
                        required
                        aria-label="Codigo do pais">
                        <option value="595" {{ (old('phone_country') ?? (auth()->user()->phone_country ?? '595')) == '595' ? 'selected' : '' }}>
                            PRY (+595)
                        </option>
                        <option value="55" {{ (old('phone_country') ?? auth()->user()->phone_country) == '55' ? 'selected' : '' }}>
                            BRA (+55)
                        </option>
                    </select>

                    <input id="register_phone_number" type="text" name="phone"
                        value="{{ old('phone') ?? auth()->user()->phone_number }}" 
                        placeholder="{{ __('messages.telefone') }}" required
                        inputmode="tel"
                        class="sax-form-control sax-auth-phone-number @error('phone') is-invalid @enderror" />
                </div>

                @error('phone')
                    <span class="invalid-feedback" role="alert" style="display: block;">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <button type="button" class="sax-btn-prev" onclick="prevStep(1)">
            <i class="fa fa-arrow-left me-2"></i> {{ __('messages.voltar') }}
        </button>
        <button type="button" class="sax-btn-next" onclick="nextStep(2)">
            {{ __('messages.continuar_envio') }} <i class="fa fa-arrow-right ms-2"></i>
        </button>
    </div>
</div>