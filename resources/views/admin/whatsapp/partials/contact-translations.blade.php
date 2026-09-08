@php
    $translatedContact = $contact ?? null;
    $translationId = $inputPrefix ?? 'whatsappContact';
@endphp

<details class="whatsapp-admin-translations">
    <summary>
        <i class="fa-solid fa-language" aria-hidden="true"></i>
        {{ __('messages.whatsapp_admin_translations') }}
    </summary>
    <div class="whatsapp-admin-translations__grid">
        @foreach (['en' => 'whatsapp_admin_english', 'es' => 'whatsapp_admin_spanish'] as $code => $languageKey)
            <fieldset class="whatsapp-admin-language">
                <legend>{{ __('messages.'.$languageKey) }}</legend>
                <div class="row g-3">
                    <div class="col-12 col-lg-7">
                        <label for="{{ $translationId }}Title{{ strtoupper($code) }}" class="form-label">{{ __('messages.whatsapp_admin_number_title') }}</label>
                        <input id="{{ $translationId }}Title{{ strtoupper($code) }}" type="text" name="title_{{ $code }}"
                               class="form-control" maxlength="120" value="{{ old('title_'.$code, $translatedContact?->{'title_'.$code}) }}">
                    </div>
                    <div class="col-12 col-lg-5">
                        <label for="{{ $translationId }}Category{{ strtoupper($code) }}" class="form-label">{{ __('messages.whatsapp_admin_category') }}</label>
                        <input id="{{ $translationId }}Category{{ strtoupper($code) }}" type="text" name="category_{{ $code }}"
                               class="form-control" maxlength="80" value="{{ old('category_'.$code, $translatedContact?->{'category_'.$code}) }}">
                    </div>
                    <div class="col-12">
                        <label for="{{ $translationId }}Description{{ strtoupper($code) }}" class="form-label">{{ __('messages.whatsapp_admin_short_description') }}</label>
                        <input id="{{ $translationId }}Description{{ strtoupper($code) }}" type="text" name="description_{{ $code }}"
                               class="form-control" maxlength="240" value="{{ old('description_'.$code, $translatedContact?->{'description_'.$code}) }}">
                    </div>
                    <div class="col-12">
                        <label for="{{ $translationId }}Message{{ strtoupper($code) }}" class="form-label">{{ __('messages.whatsapp_admin_prefilled_message') }}</label>
                        <textarea id="{{ $translationId }}Message{{ strtoupper($code) }}" name="message_{{ $code }}"
                                  class="form-control" rows="3" maxlength="1000">{{ old('message_'.$code, $translatedContact?->{'message_'.$code}) }}</textarea>
                    </div>
                </div>
            </fieldset>
        @endforeach
    </div>
</details>
