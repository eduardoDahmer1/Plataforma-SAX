<div class="step" id="step3">
    @php
        $selectedShipping = (int) old('shipping', 1);
        $selectedCountry = old('country', 'brasil');
        $selectedStore = old('store', 1);
    @endphp
    <div class="sax-checkout-box">
        <h4 class="sax-step-title">
            <span class="step-number">03</span> {{ __('messages.passo_metodo_entrega') }}
        </h4>

        {{-- Opções de envio em Cards --}}
        <div class="sax-shipping-grid mb-4">
            <label class="sax-method-card {{ $selectedShipping === 1 ? 'active' : '' }}" id="label-ship-1">
                <input type="radio" name="shipping" value="1" {{ $selectedShipping === 1 ? 'checked' : '' }} onclick="handleShippingSelection(1)">
                <div class="method-icon"><i class="fa fa-home"></i></div>
                <div class="method-text">{{ __('messages.endereco_cadastrado') }}</div>
            </label>

            <label class="sax-method-card {{ $selectedShipping === 2 ? 'active' : '' }}" id="label-ship-2">
                <input type="radio" name="shipping" value="2" {{ $selectedShipping === 2 ? 'checked' : '' }} onclick="handleShippingSelection(2)">
                <div class="method-icon"><i class="fa fa-map-marker-alt"></i></div>
                <div class="method-text">{{ __('messages.novo_endereco') }}</div>
            </label>

            <label class="sax-method-card {{ $selectedShipping === 3 ? 'active' : '' }}" id="label-ship-3">
                <input type="radio" name="shipping" value="3" {{ $selectedShipping === 3 ? 'checked' : '' }} onclick="handleShippingSelection(3)">
                <div class="method-icon"><i class="fa fa-store"></i></div>
                <div class="method-text">{{ __('messages.retirar_na_loja') }}</div>
            </label>
        </div>

        {{-- 1. Endereço Cadastrado --}}
        <div id="shipping_registered" class="sax-address-preview p-3 mb-3">
            <span class="d-block text-muted x-small mb-1">{{ __('messages.enviar_para') }}</span>
            <p class="mb-0 fw-bold">
                {{ auth()->user()->address ?? __('messages.nenhum_endereco') }}, 
                {{ auth()->user()->city ?? '' }} - {{ auth()->user()->state ?? '' }}
            </p>
        </div>

        {{-- 2. Endereço Alternativo --}}
        <div id="shipping_alternative" class="mt-4" style="display:none;">
            <div class="row g-3">
                <div class="col-12">
                    <label class="sax-label">{{ __('messages.escolha_o_pais') }}</label>
                    <select name="country" id="country" class="sax-form-control" onchange="toggleCountryFields()">
                        <option value="brasil" {{ $selectedCountry === 'brasil' ? 'selected' : '' }}>Brasil</option>
                        <option value="paraguai" {{ $selectedCountry === 'paraguai' ? 'selected' : '' }}>Paraguai</option>
                    </select>
                </div>

                <div id="brasil_address" class="row g-3 mx-0 px-0">
                    <div class="col-md-4">
                        <label class="sax-label">{{ __('messages.cep') }}</label>
                        <input type="text" name="cep" class="sax-form-control" placeholder="00000-000" value="{{ old('cep') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-label">{{ __('messages.rua') }}</label>
                        <input type="text" name="street" class="sax-form-control" value="{{ old('street') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="sax-label">{{ __('messages.numero_casa') }}</label>
                        <input type="text" name="number" class="sax-form-control" value="{{ old('number') }}">
                    </div>
                </div>

                <div id="paraguai_address" style="display:none;" class="col-12">
                    <label class="sax-label">{{ __('messages.codigo_casa_py') }}</label>
                    <input type="text" name="house_code" class="sax-form-control" placeholder="Ex: 1234" value="{{ old('house_code') }}">
                </div>
            </div>
        </div>

        {{-- 3. Retirar na Loja --}}
        <div id="shipping_store" class="mt-4" style="display:none;">
            <label class="sax-label">{{ __('messages.escolha_loja_retirada') }}</label>
            <select name="store" id="storeSelect" class="sax-form-control mb-3" onchange="updateStoreMap()">
                <option value="1" {{ (string) $selectedStore === '1' ? 'selected' : '' }}>SAX Ciudad del Este</option>
                <option value="2" {{ (string) $selectedStore === '2' ? 'selected' : '' }}>SAX Assunção</option>
            </select>

            <div id="storeMaps" class="sax-map-container rounded overflow-hidden">
                <div class="store-map" id="map-1">
                    <iframe src="https://www.google.com/maps/embed?..." width="100%" height="250" style="border:0;" allowfullscreen=""></iframe>
                </div>
                <div class="store-map" id="map-2" style="display:none;">
                    <iframe src="https://www.google.com/maps/embed?..." width="100%" height="250" style="border:0;" allowfullscreen=""></iframe>
                </div>
            </div>
        </div>

        {{-- Observações --}}
        <div class="mt-4">
            <label class="sax-label">{{ __('messages.observacoes_entrega') }}</label>
            <textarea name="observations" class="sax-form-control" rows="2">{{ old('observations') ?? auth()->user()->observations }}</textarea>
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