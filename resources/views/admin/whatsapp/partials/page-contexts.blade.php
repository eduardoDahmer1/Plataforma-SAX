<fieldset>
    <legend class="form-label mb-2">{{ __('messages.whatsapp_admin_pages_question') }}</legend>
    <div class="whatsapp-page-contexts" id="{{ $inputId }}">
        @foreach ($pageContexts as $contextKey => $contextLabel)
            <label class="whatsapp-page-context">
                <input class="form-check-input m-0" type="checkbox" name="page_contexts[]" value="{{ $contextKey }}"
                       data-whatsapp-context @if ($contextKey === 'all') data-whatsapp-context-all @endif
                       @checked(in_array($contextKey, $selectedContexts, true))>
                <span>{{ $contextLabel }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
