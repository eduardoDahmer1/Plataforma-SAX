<section class="mb-5 rendix-only border-start border-3 border-success ps-4"
         style="{{ isset($method) && ($method->is_rendix_pix ?? false) ? '' : 'display:none' }}">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h6 class="x-small fw-bold text-uppercase text-dark tracking-tighter mb-1">{{ __('messages.rendix_admin_title') }}</h6>
            <p class="small text-muted mb-0">{{ __('messages.rendix_admin_description') }}</p>
        </div>
        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ __('messages.rendix_admin_badge') }}</span>
    </div>

    <div class="form-check form-switch mb-4">
        <input type="checkbox" name="rendix_sandbox" value="1" class="form-check-input cursor-pointer"
               id="rendix_sandbox"
               {{ old('rendix_sandbox', isset($method) ? ($method->settings['sandbox'] ?? true) : true) ? 'checked' : '' }}>
        <label class="form-check-label x-small fw-bold text-uppercase ms-2 cursor-pointer" for="rendix_sandbox">
            {{ __('messages.rendix_admin_use_sandbox') }}
        </label>
        <small class="text-muted x-small mt-2 d-block italic">
            {{ __('messages.rendix_admin_sandbox_help') }}
        </small>
    </div>

    <div class="border rounded-3 p-3 p-md-4 mb-4 bg-light">
        <h6 class="small fw-bold mb-3">{{ __('messages.rendix_admin_sandbox_credentials') }}</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="sandbox_email" class="sax-form-label">{{ __('messages.rendix_admin_email') }}</label>
                <input type="email" name="sandbox_email" id="sandbox_email" class="form-control sax-input"
                       value="{{ old('sandbox_email', $method->sandbox_email ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="sandbox_merchant_id" class="sax-form-label">{{ __('messages.rendix_admin_merchant_id') }}</label>
                <input type="number" min="1" name="sandbox_merchant_id" id="sandbox_merchant_id"
                       class="form-control sax-input font-monospace"
                       value="{{ old('sandbox_merchant_id', $method->sandbox_merchant_id ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="sandbox_password" class="sax-form-label">{{ __('messages.rendix_admin_password') }}</label>
                <input type="password" name="sandbox_password" id="sandbox_password"
                       class="form-control sax-input font-monospace"
                       placeholder="{{ ($method->has_sandbox_password ?? false) ? __('messages.rendix_admin_password_saved') : __('messages.rendix_admin_sandbox_password') }}"
                       autocomplete="new-password">
            </div>
            <div class="col-md-6">
                <label for="sandbox_base_url" class="sax-form-label">{{ __('messages.rendix_admin_sandbox_url') }}</label>
                <input type="url" name="sandbox_base_url" id="sandbox_base_url"
                       class="form-control sax-input font-monospace small"
                       value="{{ old('sandbox_base_url', $method->sandbox_base_url ?? \App\Services\RendixPixService::SANDBOX_URL) }}">
            </div>
        </div>
    </div>

    <details class="border rounded-3 p-3 p-md-4 mb-4">
        <summary class="small fw-bold cursor-pointer">{{ __('messages.rendix_admin_production_credentials') }}</summary>
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label for="production_email" class="sax-form-label">{{ __('messages.rendix_admin_production_email') }}</label>
                <input type="email" name="production_email" id="production_email" class="form-control sax-input"
                       value="{{ old('production_email', $method->production_email ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="production_merchant_id" class="sax-form-label">{{ __('messages.rendix_admin_production_merchant_id') }}</label>
                <input type="number" min="1" name="production_merchant_id" id="production_merchant_id"
                       class="form-control sax-input font-monospace"
                       value="{{ old('production_merchant_id', $method->production_merchant_id ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="production_password" class="sax-form-label">{{ __('messages.rendix_admin_production_password') }}</label>
                <input type="password" name="production_password" id="production_password"
                       class="form-control sax-input font-monospace"
                       placeholder="{{ ($method->has_production_password ?? false) ? __('messages.rendix_admin_password_saved') : __('messages.rendix_admin_production_password_placeholder') }}"
                       autocomplete="new-password">
            </div>
            <div class="col-md-6">
                <label for="production_base_url" class="sax-form-label">{{ __('messages.rendix_admin_production_url') }}</label>
                <input type="url" name="production_base_url" id="production_base_url"
                       class="form-control sax-input font-monospace small"
                       placeholder="{{ __('messages.rendix_admin_provided_by_rendix') }}"
                       value="{{ old('production_base_url', $method->production_base_url ?? '') }}">
            </div>
        </div>
    </details>

    <div class="row g-3">
        <div class="col-md-4">
            <label for="operation_code" class="sax-form-label">{{ __('messages.rendix_admin_operation_code') }}</label>
            <input type="number" min="1" name="operation_code" id="operation_code"
                   class="form-control sax-input"
                   value="{{ old('operation_code', $method->operation_code ?? 1) }}">
            <small class="text-muted">{{ __('messages.rendix_admin_operation_code_help') }}</small>
        </div>
        <div class="col-md-8">
            <label for="beneficiary" class="sax-form-label">{{ __('messages.rendix_admin_beneficiary') }}</label>
            <input type="text" name="beneficiary" id="beneficiary" class="form-control sax-input"
                   value="{{ old('beneficiary', $method->beneficiary ?? __('messages.rendix_admin_default_beneficiary')) }}">
        </div>
    </div>
</section>
