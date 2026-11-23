@php
    $registrationProtection = app(\App\Services\RegistrationProtectionService::class);
    $registrationProtectionToken = $registrationProtection->issue(request());
    $turnstileEnabled = (bool) config('services.turnstile.enabled');
@endphp

<input type="hidden" name="_registration_token" value="{{ $registrationProtectionToken }}">
<div aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden;">
    <label for="company_website_{{ $registrationProtectionToken }}">Não preencha este campo</label>
    <input id="company_website_{{ $registrationProtectionToken }}" type="text" name="company_website" value="" tabindex="-1" autocomplete="off">
</div>

@if($turnstileEnabled)
    <div class="mb-3">
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-action="register" data-theme="light"></div>
    </div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif

@error('registration')
    <div class="text-danger small mb-3">{{ $message }}</div>
@enderror
