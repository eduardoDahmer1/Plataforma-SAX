<div class="step" id="step2">
    <div class="sax-checkout-box">
        <h4 class="sax-step-title">
            <span class="step-number">02</span> {{ __('messages.passo_dados_pessoais') }}
        </h4>
        
        <div class="row g-4">
            <div class="col-md-6 sax-input-group">
                <label>{{ __('messages.nome_completo') }} *</label>
                <input type="text" name="name" class="sax-form-control" 
                       value="{{ old('name') ?? auth()->user()->name }}">
            </div>

            <div class="col-md-6 sax-input-group">
                <label>{{ __('messages.documento_identidade') }} *</label>
                <input type="text" name="document" class="sax-form-control" 
                       value="{{ old('document') ?? auth()->user()->document }}">
            </div>

            <div class="col-md-6 sax-input-group">
                <label>{{ __('messages.email') }} *</label>
                <input type="email" name="email" class="sax-form-control" 
                       value="{{ old('email') ?? auth()->user()->email }}">
            </div>

            <div class="col-md-6 sax-input-group">
                <label>{{ __('messages.telefone') }} *</label>
                <input type="text" name="phone" class="sax-form-control" 
                       value="{{ old('phone') ?? auth()->user()->phone_number }}">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <button type="button" class="sax-btn-prev" onclick="prevStep(1)">
            {{ __('messages.voltar') }}
        </button>
        <button type="button" class="sax-btn-next" onclick="nextStep(2)">
            {{ __('messages.continuar_envio') }}
        </button>
    </div>
</div>