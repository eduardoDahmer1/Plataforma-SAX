<div class="step" id="step3">
    @php
        $u = auth()->user();
        $selectedShipping = (int) old('shipping', 1);
        $selectedCountry = old('country', 'brasil');
        $selectedStore = old('store', 1);

        // Montagem do endereço completo do banco para a "Dirección Registrada"
        $fullAddress = $u->address;
        if($u->number) $fullAddress .= ', ' . $u->number;
        if($u->complement) $fullAddress .= ' (' . $u->complement . ')';
        if($u->district) $fullAddress .= ' - ' . $u->district;
        if($u->city || $u->state) $fullAddress .= ' | ' . $u->city . ' - ' . $u->state;
    @endphp
    
    <div class="sax-checkout-box">
        <h4 class="sax-step-title">
            <span class="step-number">03</span> {{ __('messages.passo_metodo_entrega') }}
        </h4>

        {{-- Opções de envio em Cards --}}
        <div class="sax-shipping-grid mb-4">
            <label class="sax-method-card {{ $selectedShipping === 1 ? 'active' : '' }}" id="label-ship-1">
                <input type="radio" name="shipping" value="1" {{ $selectedShipping === 1 ? 'checked' : '' }}>
                <div class="method-icon"><i class="fa fa-home"></i></div>
                <div class="method-text">{{ __('messages.endereco_cadastrado') }}</div>
            </label>

            <label class="sax-method-card {{ $selectedShipping === 2 ? 'active' : '' }}" id="label-ship-2">
                <input type="radio" name="shipping" value="2" {{ $selectedShipping === 2 ? 'checked' : '' }}>
                <div class="method-icon"><i class="fa fa-map-marker-alt"></i></div>
                <div class="method-text">{{ __('messages.novo_endereco') }}</div>
            </label>

            <label class="sax-method-card {{ $selectedShipping === 3 ? 'active' : '' }}" id="label-ship-3">
                <input type="radio" name="shipping" value="3" {{ $selectedShipping === 3 ? 'checked' : '' }}>
                <div class="method-icon"><i class="fa fa-store"></i></div>
                <div class="method-text">{{ __('messages.retirar_na_loja') }}</div>
            </label>
        </div>

        {{-- INFO BOX DINÂMICA --}}
        <div id="shipping-info-box" class="mb-4" style="display:none;">
            <div class="p-3 rounded border" style="background-color: #f8f9fa; border-color: #dee2e6;">
                <div class="d-flex align-items-center">
                    <i class="fa fa-info-circle me-3 text-dark" style="font-size: 1.2rem;"></i>
                    <div id="shipping-info-content" class="small text-dark"></div>
                </div>
            </div>
        </div>

        {{-- 1. Endereço Cadastrado --}}
        <div id="shipping_registered" class="sax-address-preview p-3 mb-3" style="{{ $selectedShipping === 1 ? '' : 'display:none;' }}">
            <span class="d-block text-muted x-small mb-1">{{ __('messages.enviar_para') }}</span>
            <p class="mb-0 fw-bold">
                @if($u->address)
                    {{ $fullAddress }}
                @else
                    {{ __('messages.nenhum_endereco') }}
                @endif
            </p>
        </div>

        {{-- 2. Endereço Alternativo --}}
        <div id="shipping_alternative" class="mt-4" style="{{ $selectedShipping === 2 ? '' : 'display:none;' }}">
            <div class="row g-3">
                <div class="col-12">
                    <label class="sax-label">{{ __('messages.escolha_o_pais') }}</label>
                    <select name="country" id="country" class="sax-form-control">
                        <option value="brasil" {{ $selectedCountry === 'brasil' ? 'selected' : '' }}>Brasil</option>
                        <option value="paraguai" {{ $selectedCountry === 'paraguai' ? 'selected' : '' }}>Paraguai</option>
                    </select>
                </div>

                <div id="dynamic_address_section" class="row g-3 mx-0 px-0">
                    <div class="col-md-4" id="cep_container">
                        <label class="sax-label" id="label-postal">CEP</label>
                        <input type="text" id="postal_code" name="cep" class="sax-form-control" placeholder="00000-000" value="{{ old('cep') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="sax-label" id="label-state">Estado</label>
                        <select id="state-select" name="state" class="sax-form-control">
                            <option value="">Selecione...</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="sax-label">Cidade</label>
                        <select id="city-select" name="city" class="sax-form-control" disabled>
                            <option value="">Selecione o estado primeiro...</option>
                        </select>
                    </div>

                    <div class="col-md-9">
                        <label class="sax-label">{{ __('messages.rua') }}</label>
                        <input type="text" name="street" class="sax-form-control" value="{{ old('street') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="sax-label">{{ __('messages.numero_casa') }}</label>
                        <input type="text" name="number" class="sax-form-control" value="{{ old('number') }}">
                    </div>
                    
                    {{-- Novos campos para endereço alternativo se necessário --}}
                    <div class="col-md-6">
                        <label class="sax-label">{{ __('messages.bairro') ?? 'Bairro' }}</label>
                        <input type="text" name="district" class="sax-form-control" value="{{ old('district') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-label">{{ __('messages.complemento') ?? 'Complemento' }}</label>
                        <input type="text" name="complement" class="sax-form-control" value="{{ old('complement') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Retirar na Loja --}}
        <div id="shipping_store" class="mt-4" style="{{ $selectedShipping === 3 ? '' : 'display:none;' }}">
            <label class="sax-label">{{ __('messages.escolha_loja_retirada') }}</label>
            <select name="store" id="storeSelect" class="sax-form-control mb-3">
                <option value="1" {{ (string) $selectedStore === '1' ? 'selected' : '' }}>SAX Ciudad del Este</option>
                <option value="2" {{ (string) $selectedStore === '2' ? 'selected' : '' }}>SAX Assunção</option>
                <option value="3" {{ (string) $selectedStore === '3' ? 'selected' : '' }}>SAX Pedro Juan</option>
            </select>

            <div id="storeMaps" class="sax-map-container rounded overflow-hidden">
                <div class="store-map" id="map-1">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3258.616280422656!2d-54.60985242460801!3d-25.508774677511763!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94f69aaaec5ef03d%3A0xff12a8b090a63ebd!2sSAX%20Department%20Store!5e1!3m2!1spt-BR!2spy!4v1776287352177!5m2!1spt-BR!2spy" width="100%" height="250" style="border:0;" allowfullscreen=""></iframe>
                </div>
                <div class="store-map" id="map-2" style="display:none;">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d104484.94404298229!2d-57.59599500000001!3d-25.266778!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x945da76681b0d661%3A0x2e9754f73b54e3a5!2sSAX%20Department%20Store%20-%20Asunci%C3%B3n!5e1!3m2!1spt-BR!2spy!4v1776287413217!5m2!1spt-BR!2spy" width="100%" height="250" style="border:0;" allowfullscreen=""></iframe>
                </div>
                <div class="store-map" id="map-3" style="display:none;">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d213394.1864020946!2d-55.713620000000006!3d-22.560248!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94626f0079a38969%3A0xc5b346bd463b3b48!2sSAX%20Department%20Store%20-%20Pedro%20Juan%20Caballero!5e1!3m2!1spt-BR!2spy!4v1776287454037!5m2!1spt-BR!2spy" width="100%" height="250" style="border:0;" allowfullscreen=""></iframe>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label class="sax-label">{{ __('messages.observacoes_entrega') }}</label>
            <textarea id="observations" name="observations" class="sax-form-control" rows="2">{{ old('observations') ?? $u->observations }}</textarea>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <button type="button" class="sax-btn-prev" onclick="prevStep(2)">
            <i class="fa fa-arrow-left me-2"></i> {{ __('messages.voltar') }}
        </button>
        <button type="button" class="sax-btn-next" onclick="nextStep(3)">
            {{ __('messages.continuar_pagamento') }} <i class="fa fa-arrow-right ms-2"></i>
        </button>
    </div>
</div>