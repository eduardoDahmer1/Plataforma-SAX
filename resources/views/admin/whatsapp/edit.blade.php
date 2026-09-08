@extends('layout.admin')

@section('title', __('messages.whatsapp_admin_title').' - SAX')

@section('content')
<x-admin.card>
    <x-admin.page-header
        :title="__('messages.whatsapp_admin_title')"
        :description="__('messages.whatsapp_admin_description')">
        <x-slot:actions>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.contact-guide.index') }}" class="btn btn-outline-dark px-3">
                    <i class="fa-solid fa-map-location-dot me-2"></i>{{ __('messages.whatsapp_admin_guide_button') }}
                </a>
                <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-dark px-3">
                    <i class="fa-regular fa-image me-2"></i>{{ __('messages.whatsapp_admin_edit_icon') }}
                </a>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="alert alert-info border-0 mb-4">
        <i class="fa-solid fa-circle-info me-2"></i>
        {{ __('messages.whatsapp_admin_info') }}
    </div>

    <section class="whatsapp-admin-section mb-4">
        <div class="whatsapp-admin-section__heading">
            <div>
                <span class="whatsapp-admin-kicker">{{ __('messages.whatsapp_admin_menu_header') }}</span>
                <h2>{{ __('messages.whatsapp_admin_presentation') }}</h2>
            </div>
            <span class="badge {{ $settings->enabled ? 'text-bg-success' : 'text-bg-secondary' }}">
                {{ __($settings->enabled ? 'messages.whatsapp_admin_enabled' : 'messages.whatsapp_admin_disabled') }}
            </span>
        </div>

        <form action="{{ route('admin.whatsapp.settings.update') }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-12 col-lg-5">
                <label for="whatsappTitle" class="form-label">{{ __('messages.whatsapp_admin_main_title') }}</label>
                <input id="whatsappTitle" type="text" name="widget_title" class="form-control"
                       value="{{ old('widget_title', $settings->title) }}" maxlength="100" required>
            </div>
            <div class="col-12 col-lg-5">
                <label for="whatsappSubtitle" class="form-label">{{ __('messages.whatsapp_admin_support_text') }}</label>
                <input id="whatsappSubtitle" type="text" name="widget_subtitle" class="form-control"
                       value="{{ old('widget_subtitle', $settings->subtitle) }}" maxlength="180" required>
            </div>
            <div class="col-12 col-lg-2 d-flex align-items-end">
                <label class="whatsapp-admin-switch w-100">
                    <span>{{ __('messages.whatsapp_admin_show_site') }}</span>
                    <input type="hidden" name="widget_enabled" value="0">
                    <input class="form-check-input" type="checkbox" name="widget_enabled" value="1" @checked(old('widget_enabled', $settings->enabled))>
                </label>
            </div>
            <div class="col-12">
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
                                    <div class="col-12 col-lg-5">
                                        <label for="whatsappTitle{{ strtoupper($code) }}" class="form-label">{{ __('messages.whatsapp_admin_main_title') }}</label>
                                        <input id="whatsappTitle{{ strtoupper($code) }}" type="text" name="widget_title_{{ $code }}" class="form-control"
                                               value="{{ old('widget_title_'.$code, $settings->{'title_'.$code}) }}" maxlength="100">
                                    </div>
                                    <div class="col-12 col-lg-7">
                                        <label for="whatsappSubtitle{{ strtoupper($code) }}" class="form-label">{{ __('messages.whatsapp_admin_support_text') }}</label>
                                        <input id="whatsappSubtitle{{ strtoupper($code) }}" type="text" name="widget_subtitle_{{ $code }}" class="form-control"
                                               value="{{ old('widget_subtitle_'.$code, $settings->{'subtitle_'.$code}) }}" maxlength="180">
                                    </div>
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                </details>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-dark px-4">
                    <i class="fa-solid fa-floppy-disk me-2"></i>{{ __('messages.whatsapp_admin_save_presentation') }}
                </button>
            </div>
        </form>
    </section>

    <section class="whatsapp-admin-section mb-4">
        <div class="whatsapp-admin-section__heading">
            <div>
                <span class="whatsapp-admin-kicker">{{ __('messages.whatsapp_admin_new_destination') }}</span>
                <h2>{{ __('messages.whatsapp_admin_add_number') }}</h2>
            </div>
        </div>

        <form action="{{ route('admin.whatsapp.contacts.store') }}" method="POST" class="row g-3" data-whatsapp-contact-form>
            @csrf
            <div class="col-12 col-lg-4">
                <label for="newWhatsappTitle" class="form-label">{{ __('messages.whatsapp_admin_number_title') }}</label>
                <input id="newWhatsappTitle" type="text" name="title" class="form-control"
                       value="{{ old('title') }}" placeholder="Ex.: Funcionário Adriano — Vendas" maxlength="120" required>
            </div>
            <div class="col-12 col-md-6 col-lg-2">
                <label for="newWhatsappCategory" class="form-label">{{ __('messages.whatsapp_admin_category') }}</label>
                <input id="newWhatsappCategory" type="text" name="category" class="form-control"
                       value="{{ old('category', 'Atendimento') }}" placeholder="Ex.: Pisos" maxlength="80" required>
            </div>
            <div class="col-12 col-lg-3">
                <label for="newWhatsappPhone" class="form-label">{{ __('messages.whatsapp_admin_number_ddi') }}</label>
                <input id="newWhatsappPhone" type="tel" name="phone" class="form-control"
                       value="{{ old('phone') }}" placeholder="+55 45 95162-1545" maxlength="30" required>
            </div>
            <div class="col-6 col-lg-2">
                <label for="newWhatsappOrder" class="form-label">{{ __('messages.whatsapp_admin_order') }}</label>
                <input id="newWhatsappOrder" type="number" name="sort_order" class="form-control" min="0" max="100000"
                       value="{{ old('sort_order', ($contacts->max('sort_order') ?? 0) + 10) }}">
            </div>
            <div class="col-6 col-lg-1 d-flex align-items-end">
                <label class="whatsapp-admin-switch w-100">
                    <span>{{ __('messages.whatsapp_admin_active') }}</span>
                    <input type="hidden" name="active" value="0">
                    <input class="form-check-input" type="checkbox" name="active" value="1" @checked(old('active', true))>
                </label>
            </div>
            <div class="col-12 col-lg-6 whatsapp-admin-balanced-column">
                <div>
                    <label for="newWhatsappDescription" class="form-label">{{ __('messages.whatsapp_admin_short_description') }}</label>
                    <input id="newWhatsappDescription" type="text" name="description" class="form-control"
                           value="{{ old('description') }}" maxlength="240"
                           placeholder="Ex.: Atendimento para marcas e produtos localizados no Piso 4.">
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="newWhatsappIcon" class="form-label">{{ __('messages.whatsapp_admin_contact_icon') }}</label>
                        <select id="newWhatsappIcon" name="icon" class="form-select" required>
                            @foreach ($iconOptions as $iconClass => $iconLabel)
                                <option value="{{ $iconClass }}" @selected(old('icon', 'fa-headset') === $iconClass)>{{ $iconLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 d-flex align-items-end">
                        <label class="whatsapp-admin-switch w-100">
                            <span>{{ __('messages.whatsapp_admin_show_contact_page') }}</span>
                            <input type="hidden" name="show_on_contact_page" value="0">
                            <input class="form-check-input" type="checkbox" name="show_on_contact_page" value="1"
                                   @checked(old('show_on_contact_page', true))>
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6 whatsapp-admin-message-field">
                <label for="newWhatsappMessage" class="form-label">{{ __('messages.whatsapp_admin_prefilled_message') }}</label>
                <textarea id="newWhatsappMessage" name="message" class="form-control" rows="5" maxlength="1000"
                          placeholder="Ex.: Olá Adriano, gostaria de falar sobre uma compra.">{{ old('message') }}</textarea>
            </div>
            <div class="col-12">
                @include('admin.whatsapp.partials.contact-translations', [
                    'inputPrefix' => 'newWhatsapp',
                    'contact' => null,
                ])
            </div>
            <div class="col-12">
                @include('admin.whatsapp.partials.page-contexts', [
                    'inputId' => 'newWhatsappPages',
                    'selectedContexts' => old('page_contexts', ['all']),
                ])
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-dark px-4">
                    <i class="fa-solid fa-plus me-2"></i>{{ __('messages.whatsapp_admin_add_contact') }}
                </button>
            </div>
        </form>
    </section>

    <div class="whatsapp-admin-list-heading">
        <div>
            <span class="whatsapp-admin-kicker">{{ __('messages.whatsapp_admin_destinations') }}</span>
            <h2>{{ __('messages.whatsapp_admin_contacts_count', ['count' => $contacts->count()]) }}</h2>
        </div>
        <small>{{ __('messages.whatsapp_admin_order_hint') }}</small>
    </div>

    <div class="whatsapp-admin-list">
        @forelse ($contacts as $contact)
            <article class="whatsapp-admin-contact {{ $contact->active ? '' : 'is-inactive' }}">
                <div class="whatsapp-admin-contact__summary">
                    <span class="whatsapp-admin-contact__icon"><i class="fa-brands fa-whatsapp"></i></span>
                    <div>
                        <strong>{{ $contact->title }}</strong>
                        <span>{{ $contact->phone }}</span>
                    </div>
                    <div class="whatsapp-admin-contact__meta">
                        <span>{{ __('messages.whatsapp_admin_order') }} {{ $contact->sort_order }}</span>
                        <span class="{{ $contact->active ? 'is-active' : '' }}">{{ __($contact->active ? 'messages.ativo' : 'messages.inativo') }}</span>
                    </div>
                </div>

                <form action="{{ route('admin.whatsapp.contacts.update', $contact) }}" method="POST"
                      class="row g-3 whatsapp-admin-contact__form" data-whatsapp-contact-form>
                    @csrf
                    @method('PUT')
                    <div class="col-12 col-lg-4">
                        <label for="contactTitle{{ $contact->id }}" class="form-label">{{ __('messages.whatsapp_admin_number_title') }}</label>
                        <input id="contactTitle{{ $contact->id }}" type="text" name="title" class="form-control"
                               value="{{ $contact->title }}" maxlength="120" required>
                    </div>
                    <div class="col-12 col-md-6 col-lg-2">
                        <label for="contactCategory{{ $contact->id }}" class="form-label">{{ __('messages.whatsapp_admin_category') }}</label>
                        <input id="contactCategory{{ $contact->id }}" type="text" name="category" class="form-control"
                               value="{{ $contact->category }}" maxlength="80" required>
                    </div>
                    <div class="col-12 col-lg-3">
                        <label for="contactPhone{{ $contact->id }}" class="form-label">{{ __('messages.telefone') }}</label>
                        <input id="contactPhone{{ $contact->id }}" type="tel" name="phone" class="form-control"
                               value="{{ $contact->phone }}" maxlength="30" required>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label for="contactOrder{{ $contact->id }}" class="form-label">{{ __('messages.whatsapp_admin_order') }}</label>
                        <input id="contactOrder{{ $contact->id }}" type="number" name="sort_order" class="form-control"
                               value="{{ $contact->sort_order }}" min="0" max="100000">
                    </div>
                    <div class="col-6 col-lg-1 d-flex align-items-end">
                        <label class="whatsapp-admin-switch w-100">
                            <span>{{ __('messages.whatsapp_admin_active') }}</span>
                            <input type="hidden" name="active" value="0">
                            <input class="form-check-input" type="checkbox" name="active" value="1" @checked($contact->active)>
                        </label>
                    </div>
                    <div class="col-12 col-lg-6 whatsapp-admin-balanced-column">
                        <div>
                            <label for="contactDescription{{ $contact->id }}" class="form-label">{{ __('messages.admin_guide_short_description') }}</label>
                            <input id="contactDescription{{ $contact->id }}" type="text" name="description" class="form-control"
                                   value="{{ $contact->description }}" maxlength="240">
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="contactIcon{{ $contact->id }}" class="form-label">{{ __('messages.whatsapp_admin_contact_icon') }}</label>
                                <select id="contactIcon{{ $contact->id }}" name="icon" class="form-select" required>
                                    @foreach ($iconOptions as $iconClass => $iconLabel)
                                        <option value="{{ $iconClass }}" @selected($contact->icon === $iconClass)>{{ $iconLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-end">
                                <label class="whatsapp-admin-switch w-100">
                                    <span>{{ __('messages.whatsapp_admin_show_contact_page') }}</span>
                                    <input type="hidden" name="show_on_contact_page" value="0">
                                    <input class="form-check-input" type="checkbox" name="show_on_contact_page" value="1"
                                           @checked($contact->show_on_contact_page)>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 whatsapp-admin-message-field">
                        <label for="contactMessage{{ $contact->id }}" class="form-label">{{ __('messages.whatsapp_admin_prefilled_message') }}</label>
                        <textarea id="contactMessage{{ $contact->id }}" name="message" class="form-control" rows="5"
                                  maxlength="1000">{{ $contact->message }}</textarea>
                    </div>
                    <div class="col-12">
                        @include('admin.whatsapp.partials.contact-translations', [
                            'inputPrefix' => 'contact'.$contact->id,
                            'contact' => $contact,
                        ])
                    </div>
                    <div class="col-12">
                        @include('admin.whatsapp.partials.page-contexts', [
                            'inputId' => 'contactPages'.$contact->id,
                            'selectedContexts' => $contact->page_contexts ?: ['all'],
                        ])
                    </div>
                    <div class="col-12 d-flex flex-column flex-sm-row justify-content-between gap-2">
                        <a href="{{ $contact->whatsappUrl() }}" target="_blank" rel="noopener" class="btn btn-outline-success">
                            <i class="fa-brands fa-whatsapp me-2"></i>{{ __('messages.whatsapp_admin_test_redirect') }}
                        </a>
                        <button type="submit" class="btn btn-dark px-4">
                            <i class="fa-solid fa-floppy-disk me-2"></i>{{ __('messages.whatsapp_admin_save_contact') }}
                        </button>
                    </div>
                </form>

                <form action="{{ route('admin.whatsapp.contacts.destroy', $contact) }}" method="POST"
                      class="whatsapp-admin-contact__delete"
                      onsubmit="return confirm(@js(__('messages.whatsapp_admin_delete_confirm')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ __('messages.whatsapp_admin_delete_contact') }}">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </form>
            </article>
        @empty
            <div class="whatsapp-admin-empty">
                <i class="fa-brands fa-whatsapp"></i>
                <strong>{{ __('messages.whatsapp_admin_empty_title') }}</strong>
                <span>{{ __('messages.whatsapp_admin_empty_text') }}</span>
            </div>
        @endforelse
    </div>
</x-admin.card>

<style>
    .whatsapp-admin-section { padding:1.25rem; border:1px solid #e1e6ee; border-radius:14px; background:#fff; }
    .whatsapp-admin-section__heading,.whatsapp-admin-list-heading { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
    .whatsapp-admin-section__heading h2,.whatsapp-admin-list-heading h2 { margin:.15rem 0 0; color:#172033; font-size:1rem; font-weight:800; }
    .whatsapp-admin-kicker { color:#667085; font-size:.65rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .whatsapp-admin-switch { display:flex; min-height:44px; padding:.6rem .75rem; align-items:center; justify-content:space-between; gap:.5rem; border:1px solid #d9dfe8; border-radius:10px; background:#f8fafc; }
    .whatsapp-admin-switch span { color:#475467; font-size:.69rem; font-weight:800; text-transform:uppercase; }
    .whatsapp-admin-list-heading { margin:1.5rem 0 .75rem; }
    .whatsapp-admin-list-heading small { color:#667085; }
    .whatsapp-admin-list { display:grid; gap:.85rem; }
    .whatsapp-admin-contact { position:relative; overflow:hidden; border:1px solid #dfe5ed; border-radius:14px; background:#fff; }
    .whatsapp-admin-contact.is-inactive { opacity:.72; }
    .whatsapp-admin-contact__summary { display:flex; padding:1rem 4.5rem 1rem 1rem; align-items:center; gap:.8rem; border-bottom:1px solid #edf0f4; background:#f8fafc; }
    .whatsapp-admin-contact__summary > div:nth-child(2) { min-width:0; flex:1; }
    .whatsapp-admin-contact__summary strong,.whatsapp-admin-contact__summary span { display:block; }
    .whatsapp-admin-contact__summary strong { color:#172033; font-size:.86rem; }
    .whatsapp-admin-contact__summary span { color:#667085; font-size:.7rem; }
    .whatsapp-admin-contact__icon { display:inline-grid; width:40px; height:40px; flex:0 0 40px; place-items:center; border-radius:10px; background:#dcfce7; color:#16a34a; font-size:1.15rem; }
    .whatsapp-admin-contact__meta { display:flex; align-items:center; gap:.35rem; }
    .whatsapp-admin-contact__meta span { padding:.25rem .5rem; border-radius:999px; background:#eef1f5; font-size:.62rem; font-weight:800; }
    .whatsapp-admin-contact__meta span.is-active { background:#dcfce7; color:#15803d; }
    .whatsapp-admin-contact__form { padding:.9rem 1rem 1rem; }
    .whatsapp-admin-contact__delete { position:absolute; top:1rem; right:1rem; margin:0; }
    .whatsapp-admin-balanced-column { display:flex; flex-direction:column; justify-content:space-between; gap:.85rem; }
    .whatsapp-admin-message-field { display:flex; flex-direction:column; }
    .whatsapp-admin-message-field textarea { min-height:124px; flex:1; resize:vertical; }
    .whatsapp-admin-translations { overflow:hidden; border:1px solid #dfe5ed; border-radius:12px; background:#f8fafc; }
    .whatsapp-admin-translations summary { display:flex; padding:.8rem 1rem; align-items:center; gap:.55rem; color:#344054; font-size:.72rem; font-weight:800; cursor:pointer; list-style:none; }
    .whatsapp-admin-translations summary::-webkit-details-marker { display:none; }
    .whatsapp-admin-translations summary::after { margin-left:auto; content:'+'; font-size:1rem; }
    .whatsapp-admin-translations[open] summary::after { content:'−'; }
    .whatsapp-admin-translations__grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; padding:0 .85rem .85rem; }
    .whatsapp-admin-language { min-width:0; padding:.85rem; border:1px solid #e5e9ef; border-radius:10px; background:#fff; }
    .whatsapp-admin-language legend { float:none; width:auto; margin:0 0 .65rem; padding:0; color:#172033; font-size:.72rem; font-weight:800; }
    .whatsapp-admin-contact__form .form-label,
    .whatsapp-admin-section form .form-label { margin-bottom:.35rem; color:#526078; font-size:.65rem; font-weight:800; letter-spacing:.035em; text-transform:uppercase; }
    .whatsapp-admin-contact__form fieldset,
    .whatsapp-admin-section form fieldset { margin:0; }
    .whatsapp-admin-contact__form legend.form-label,
    .whatsapp-admin-section form legend.form-label { width:auto; margin-bottom:.55rem; font-size:.7rem; line-height:1.2; }
    .whatsapp-page-contexts { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.45rem; }
    .whatsapp-page-context { display:flex; min-height:42px; padding:.55rem .7rem; align-items:center; gap:.45rem; border:1px solid #e1e6ee; border-radius:9px; background:#f8fafc; cursor:pointer; }
    .whatsapp-page-context span { color:#475467; font-size:.7rem; font-weight:700; text-transform:none; }
    .whatsapp-admin-empty { display:grid; min-height:200px; place-items:center; align-content:center; gap:.4rem; border:1px dashed #d5dbe5; border-radius:14px; color:#667085; text-align:center; }
    .whatsapp-admin-empty i { color:#22c55e; font-size:2rem; }
    .whatsapp-admin-empty strong { color:#172033; }
    .whatsapp-admin-empty span { font-size:.75rem; }
    @media(max-width:991px){.whatsapp-page-contexts{grid-template-columns:repeat(2,minmax(0,1fr));}.whatsapp-admin-translations__grid{grid-template-columns:1fr;}}
    @media(max-width:575px){.whatsapp-admin-section{padding:.85rem}.whatsapp-admin-section__heading,.whatsapp-admin-list-heading,.whatsapp-admin-contact__summary{align-items:flex-start;flex-direction:column}.whatsapp-admin-contact__summary{padding-right:4rem}.whatsapp-admin-contact__meta{flex-wrap:wrap}.whatsapp-admin-contact__form{padding:.75rem}.whatsapp-admin-message-field textarea{min-height:105px}.whatsapp-page-contexts{grid-template-columns:1fr}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-whatsapp-contact-form]').forEach(function (form) {
        const all = form.querySelector('[data-whatsapp-context-all]');
        const scoped = Array.from(form.querySelectorAll('[data-whatsapp-context]:not([data-whatsapp-context-all])'));
        if (!all) return;

        const sync = function (source) {
            if (source === all && all.checked) scoped.forEach(input => input.checked = false);
            if (source !== all && source.checked) all.checked = false;
            if (!all.checked && !scoped.some(input => input.checked)) all.checked = true;
        };

        [all, ...scoped].forEach(input => input.addEventListener('change', () => sync(input)));
    });
});
</script>
@endsection
