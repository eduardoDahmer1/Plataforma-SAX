@php
    $currentGatewayName = old('name', $method->name ?? '');
    $normalizedGatewayName = mb_strtolower(trim($currentGatewayName));
    $knownGatewayNames = ['bancard v2', 'rendix pix', 'pix rendix', 'pix'];
    $isCustomGatewayName = $currentGatewayName !== '' && !in_array($normalizedGatewayName, $knownGatewayNames, true);
@endphp

<label for="gateway_choice" class="sax-form-label">{{ __('messages.nome_metodo_label') }}</label>
<select name="gateway_choice" id="gateway_choice" class="form-select sax-input" required>
    <option value="" {{ $currentGatewayName === '' ? 'selected' : '' }}>{{ __('messages.gateway_select_method') }}</option>
    <option value="Bancard V2" {{ $normalizedGatewayName === 'bancard v2' ? 'selected' : '' }}>
        {{ __('messages.gateway_bancard_option') }}
    </option>
    <option value="Rendix Pix" {{ in_array($normalizedGatewayName, ['rendix pix', 'pix rendix', 'pix'], true) ? 'selected' : '' }}>
        {{ __('messages.gateway_rendix_option') }}
    </option>
    <option value="__custom__" {{ $isCustomGatewayName ? 'selected' : '' }}>{{ __('messages.gateway_other_method') }}</option>
</select>

<input type="hidden" name="name" id="name" value="{{ $currentGatewayName }}">

<div id="custom_gateway_name_wrap" class="mt-3" style="{{ $isCustomGatewayName ? '' : 'display:none' }}">
    <label for="custom_gateway_name" class="sax-form-label">{{ __('messages.gateway_custom_name') }}</label>
    <input type="text" name="custom_gateway_name" id="custom_gateway_name" class="form-control sax-input"
           placeholder="{{ __('messages.gateway_custom_placeholder') }}"
           value="{{ $isCustomGatewayName ? $currentGatewayName : '' }}">
</div>

<small class="text-muted d-block mt-2">
    {{ __('messages.gateway_rendix_help') }}
</small>
