@extends('layout.layout')

@section('content')
    <div class="contact-page-wrapper py-5">
        <div class="container">
            <div class="contact-hero text-center mb-5">
                <h1 class="contact-title">{{ __('messages.contato') }}</h1>
                <p class="contact-subtitle">{{ __('messages.estamos_disposicao') }}</p>
                @if($contactGuideEnabled ?? true)
                    <a href="{{ route('contact.guide') }}" class="contact-guide-link">
                        <i class="fa-solid {{ $isOtica ? 'fa-glasses' : 'fa-map-location-dot' }}"></i>
                        {{ __($isOtica ? 'messages.contact_form_guide_optical' : 'messages.contact_form_guide_general') }}
                    </a>
                @endif
            </div>

            @if(session('success'))
                <div class="alert alert-dark border-0 contact-alert d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="contact-validation-alert mb-4" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <div>
                        <strong>{{ __('messages.contact_form_validation_title') }}</strong>
                        <span>{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            @if($directoryContacts->isNotEmpty())
                @php
                    $directoryCategories = $directoryContacts
                        ->map(fn ($contact) => $contact->translated('category'))
                        ->filter()
                        ->unique()
                        ->values();
                @endphp
                <section class="contact-directory mb-5" aria-labelledby="contactDirectoryTitle">
                    <div class="contact-directory-head">
                        <div>
                            <span class="contact-directory-kicker">{{ __('messages.contact_form_direct_eyebrow') }}</span>
                            <h2 id="contactDirectoryTitle">{{ __('messages.contact_form_direct_title') }}</h2>
                        </div>
                        <button type="button" class="contact-directory-toggle" id="contactDirectoryToggle"
                                aria-expanded="false" aria-controls="contactDirectoryContent"
                                data-show-label="{{ __('messages.contact_form_show_contacts') }}"
                                data-hide-label="{{ __('messages.contact_form_hide_contacts') }}">
                            <span>{{ __('messages.contact_form_show_contacts') }}</span>
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="contact-directory-content" id="contactDirectoryContent" hidden>
                        <div class="contact-directory-tools">
                            <div class="contact-directory-search">
                                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                <input type="search" id="contactDirectorySearch" placeholder="{{ __('messages.contact_form_search_contacts') }}"
                                       aria-label="{{ __('messages.contact_form_search_contacts') }}">
                            </div>

                            @if($directoryCategories->count() > 1)
                                <div class="contact-directory-filters" role="group" aria-label="{{ __('messages.contact_form_filter_contacts') }}">
                                    <button type="button" class="is-active" data-contact-filter="all">{{ __('messages.contact_form_all') }}</button>
                                    @foreach($directoryCategories as $directoryCategory)
                                        <button type="button" data-contact-filter="{{ Str::slug($directoryCategory) }}">
                                            {{ $directoryCategory }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="contact-directory-grid" id="contactDirectoryGrid">
                            @foreach($directoryContacts as $directoryContact)
                                @php
                                    $directoryTitle = $directoryContact->translated('title');
                                    $directoryCategory = $directoryContact->translated('category');
                                    $directoryDescription = $directoryContact->translated('description');
                                @endphp
                                <a href="{{ $directoryContact->whatsappUrl() }}" target="_blank" rel="noopener"
                                   class="contact-directory-card"
                                   data-contact-card
                                   data-category="{{ Str::slug($directoryCategory) }}"
                                   data-search="{{ Str::lower($directoryTitle.' '.$directoryCategory.' '.$directoryDescription.' '.$directoryContact->phone) }}">
                                    <span class="contact-directory-icon">
                                        <i class="fa-solid {{ $directoryContact->icon ?: 'fa-headset' }}" aria-hidden="true"></i>
                                    </span>
                                    <span class="contact-directory-copy">
                                        <small>{{ $directoryCategory }}</small>
                                        <strong>{{ $directoryTitle }}</strong>
                                        @if($directoryDescription)
                                            <span>{{ $directoryDescription }}</span>
                                        @endif
                                        <em><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>{{ $directoryContact->phone }}</em>
                                    </span>
                                    <i class="fa-solid fa-arrow-up-right-from-square contact-directory-arrow" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </div>

                        <div class="contact-directory-empty" id="contactDirectoryEmpty" hidden>
                            <i class="fa-regular fa-face-meh" aria-hidden="true"></i>
                            <span>{{ __('messages.contact_form_no_contacts') }}</span>
                        </div>
                    </div>
                </section>
            @endif

            <div class="contact-card mb-5">
                @if($isOtica)
                    <div class="contact-form-heading">
                        <span class="contact-form-heading__icon"><i class="fa-solid fa-glasses"></i></span>
                        <div>
                            <span>{{ __('messages.contact_form_optical_eyebrow') }}</span>
                            <h2>{{ __('messages.contact_form_optical_title') }}</h2>
                            <p>{{ __('messages.contact_form_optical_description') }}</p>
                        </div>
                    </div>
                @else
                    <div class="contact-tabs" role="tablist" aria-label="{{ __('messages.contact_form_type') }}">
                        <button type="button" class="btn btn-sax-tab {{ (int) old('contact_type', 1) === 1 ? 'active' : '' }}" id="btn-atendimento" onclick="setFormType(1)">
                            {{ __('messages.atendimento') }}
                        </button>
                        <button type="button" class="btn btn-sax-tab {{ (int) old('contact_type', 1) === 2 ? 'active' : '' }}" id="btn-curriculo" onclick="setFormType(2)">
                            {{ __('messages.trabalhe_conosco') }}
                        </button>
                    </div>
                @endif

                <div class="contact-grid">
                    <form action="{{ route('contact.store') }}" method="POST" id="contactForm" enctype="multipart/form-data" class="sax-form">
                        @csrf
                        <input type="hidden" name="contact_type" id="contact_type" value="{{ $isOtica ? 4 : old('contact_type', 1) }}">

                        <div class="row g-3 g-md-4">
                            <div class="col-md-4">
                                <label class="sax-label">{{ __('messages.nome_completo') }}</label>
                                <input type="text" name="name" class="form-control sax-input" value="{{ old('name') }}" placeholder="Ex: Maria Silva" required>
                            </div>

                            <div class="col-md-4">
                                <label class="sax-label">{{ __('messages.email') }}</label>
                                <input type="email" name="email" class="form-control sax-input" value="{{ old('email') }}" placeholder="email@exemplo.com" required>
                            </div>

                            <div class="col-md-4">
                                <label class="sax-label">{{ __('messages.telefone') }}</label>
                                <input type="text" name="phone" class="form-control sax-input" value="{{ old('phone') }}" placeholder="+595 XXX XXXXXX">
                            </div>

                            @if($isOtica)
                                <div class="col-md-5">
                                    <label class="sax-label" for="opticalPrescription">{{ __('messages.contact_form_prescription_label') }}</label>
                                    <label class="optical-prescription" for="opticalPrescription">
                                        <i class="fa-solid fa-file-arrow-up"></i>
                                        <span>
                                            <strong>{{ __('messages.contact_form_select_prescription') }}</strong>
                                            <small>{{ __('messages.contact_form_file_hint') }}</small>
                                        </span>
                                    </label>
                                    <input id="opticalPrescription" class="visually-hidden" type="file" name="attachment"
                                           accept=".pdf,.jpg,.jpeg,.png,image/*" data-optical-prescription>
                                    <span class="optical-prescription-name" data-optical-prescription-name
                                          data-empty-label="{{ __('messages.contact_form_no_file') }}">{{ __('messages.contact_form_no_file') }}</span>
                                </div>
                                <div class="col-md-7">
                                    <label class="sax-label" for="opticalDescription">{{ __('messages.contact_form_describe_need') }}</label>
                                    <textarea id="opticalDescription" name="message" class="form-control sax-input"
                                              rows="5" placeholder="{{ __('messages.contact_form_optical_placeholder') }}" required>{{ old('message') }}</textarea>
                                </div>
                            @else
                                <div class="col-md-12 form-field" data-type="1 2">
                                    <label class="sax-label">{{ __('messages.mensagem') }}</label>
                                    <textarea name="message" class="form-control sax-input" rows="5" placeholder="{{ __('messages.como_ajudar') }}" required>{{ old('message') }}</textarea>
                                </div>
                            @endif

                            @unless($isOtica)
                            <div class="col-md-12 form-field" data-type="2" style="display:none;">
                                <label class="sax-label">{{ __('messages.loja_desejada') !== 'messages.loja_desejada' ? __('messages.loja_desejada') : 'LOJA DESEJADA' }}</label>
                                <select name="store_name" class="form-control sax-input">
                                    @foreach (\App\Models\Contact::STORES as $storeName)
                                        <option value="{{ $storeName }}" @selected(old('store_name') === $storeName)>{{ $storeName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12 form-field" data-type="2" style="display:none;">
                                <label class="sax-label">{{ __('messages.anexar_curriculo') ?? 'ANEXAR CURRÍCULO (PDF/IMG)' }}</label>
                                <input type="file" name="attachment" class="form-control sax-input" accept=".pdf,image/*">
                            </div>
                            @endunless

                            <div class="col-md-4 ms-auto">
                                <button type="submit" class="btn btn-sax-submit w-100">
                                    {{ __('messages.enviar') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    @if(!$isOtica && $flyers->isNotEmpty())
                        <aside class="contact-flyers form-field" data-type="2" style="display:none;">
                            <div class="contact-flyers-head">
                                <h2 class="contact-flyers-title">{{ __('messages.contact_form_jobs_title') }}</h2>
                                <p class="contact-flyers-sub">{{ __('messages.contact_form_jobs_description') }}</p>
                            </div>

                            <div class="swiper jobFlyersSwiper">
                                <div class="swiper-wrapper">
                                    @foreach ($flyers as $flyer)
                                        <div class="swiper-slide">
                                            <div class="contact-flyer-frame">
                                                <img src="{{ asset('storage/' . $flyer->image) }}" alt="Flyer Trabalhe Conosco" loading="lazy">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="job-flyers-pagination swiper-pagination"></div>
                                <div class="job-flyers-nav">
                                    <button type="button" class="job-flyers-btn job-flyers-prev" aria-label="Flyer anterior">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="job-flyers-btn job-flyers-next" aria-label="Próximo flyer">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </aside>
                    @endif
                </div>
            </div>

            <div class="contact-locations">
                <div class="text-center mb-4">
                    <h2 class="map-section-title">{{ __('messages.nossas_unidades') }}</h2>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4">
                        <article class="map-card h-100">
                            <h6 class="map-title">{{ __('messages.sax_cde') }}</h6>
                            <p class="map-address">{{ __('messages.sax_cde_endereco') }}</p>
                            <div class="map-wrapper">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1865759.3980800086!2d-57.434554686265194!3d-24.028902543292244!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94f69aaaec5ef03d%3A0xff12a8b090a63ebd!2sSAX%20Department%20Store!5e0!3m2!1spt-BR!2spy!4v1770210106773!5m2!1spt-BR!2spy" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </article>
                    </div>

                    <div class="col-lg-4">
                        <article class="map-card h-100">
                            <h6 class="map-title">{{ __('messages.sax_asuncion') }}</h6>
                            <p class="map-address">{{ __('messages.sax_asuncion_endereco') }}</p>
                            <div class="map-wrapper">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d28864.905476014617!2d-57.634103725683595!3d-25.266777699999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x945da76681b0d661%3A0x2e9754f73b54e3a5!2sSAX%20Department%20Store%20-%20Asunci%C3%B3n!5e0!3m2!1spt-BR!2spy!4v1770210083150!5m2!1spt-BR!2spy" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </article>
                    </div>

                    <div class="col-lg-4">
                        <article class="map-card h-100">
                            <h6 class="map-title">{{ __('messages.sax_pjc') }}</h6>
                            <p class="map-address">{{ __('messages.sax_pjc_endereco') }}</p>
                            <div class="map-wrapper">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d58954.282839244144!2d-55.76757534513292!3d-22.555054301589653!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94626f0079a38969%3A0xc5b346bd463b3b48!2sSAX%20Department%20Store%20-%20Pedro%20Juan%20Caballero!5e0!3m2!1spt-BR!2spy!4v1770210046629!5m2!1spt-BR!2spy" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .contact-page-wrapper {
            background: radial-gradient(circle at 0% 0%, #f7f5f1 0%, #ffffff 48%);
        }

        .contact-hero {
            max-width: 760px;
            margin: 0 auto;
        }

        .contact-title {
            margin: 0;
            font-size: clamp(1.5rem, 4.5vw, 2.8rem);
            text-transform: uppercase;
            letter-spacing: .17em;
            font-weight: 300;
            color: #141311;
        }

        .contact-subtitle {
            margin: 14px auto 0;
            max-width: 520px;
            font-size: .74rem;
            text-transform: uppercase;
            letter-spacing: .15em;
            color: #7f786d;
        }

        .contact-guide-link {
            display: inline-flex;
            margin-top: 1rem;
            padding: .55rem .8rem;
            align-items: center;
            gap: .45rem;
            border: 1px solid #ddd5c9;
            border-radius: 999px;
            color: #332d25;
            font-size: .6rem;
            font-weight: 750;
            text-decoration: none;
        }

        .contact-guide-link:hover {
            border-color: #171512;
            background: #171512;
            color: #fff;
        }

        .contact-alert {
            border-radius: 12px;
            background: #121212;
            color: #fff;
            padding: 14px 16px;
        }

        .contact-validation-alert {
            display: flex;
            padding: .85rem 1rem;
            align-items: center;
            gap: .7rem;
            border: 1px solid #e7d5cf;
            border-radius: 12px;
            background: #fff8f6;
            color: #6f3024;
        }

        .contact-validation-alert > i {
            font-size: .9rem;
        }

        .contact-validation-alert strong,
        .contact-validation-alert span {
            display: block;
        }

        .contact-validation-alert strong {
            font-size: .7rem;
        }

        .contact-validation-alert span {
            margin-top: .15rem;
            font-size: .62rem;
        }

        .contact-directory {
            padding: 1rem 1.15rem;
            border: 1px solid #e9e4da;
            border-radius: 16px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 10px 30px rgba(30, 24, 16, .045);
        }

        .contact-directory-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .contact-directory-kicker {
            display: block;
            margin-bottom: .3rem;
            color: #9b7a45;
            font-size: .62rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .contact-directory-head h2 {
            margin: 0;
            color: #171512;
            font-size: 1.12rem;
            font-weight: 750;
            letter-spacing: -.02em;
        }

        .contact-directory-toggle {
            display: inline-flex;
            min-height: 38px;
            padding: .55rem .85rem;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            border: 1px solid #ded7cc;
            border-radius: 999px;
            background: #fff;
            color: #29251f;
            font-size: .65rem;
            font-weight: 750;
            letter-spacing: .04em;
            transition: background .18s ease, color .18s ease, border-color .18s ease;
        }

        .contact-directory-toggle:hover,
        .contact-directory-toggle[aria-expanded="true"] {
            border-color: #1c1915;
            background: #1c1915;
            color: #fff;
        }

        .contact-directory-toggle i {
            font-size: .55rem;
            transition: transform .18s ease;
        }

        .contact-directory-toggle[aria-expanded="true"] i {
            transform: rotate(180deg);
        }

        .contact-directory-content {
            padding-top: 1rem;
            border-top: 1px solid #eee9e1;
            margin-top: 1rem;
        }

        .contact-directory-content[hidden] {
            display: none;
        }

        .contact-directory-tools {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
        }

        .contact-directory-search {
            position: relative;
            flex: 0 1 290px;
        }

        .contact-directory-search i {
            position: absolute;
            top: 50%;
            left: .8rem;
            color: #a49b8e;
            font-size: .72rem;
            transform: translateY(-50%);
        }

        .contact-directory-search input {
            width: 100%;
            min-height: 40px;
            padding: .55rem .8rem .55rem 2.1rem;
            border: 1px solid #e2ddd4;
            border-radius: 10px;
            background: #faf9f7;
            color: #27231e;
            font-size: .73rem;
            outline: none;
        }

        .contact-directory-search input:focus {
            border-color: #b9a78c;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(155, 122, 69, .08);
        }

        .contact-directory-filters {
            display: flex;
            padding-bottom: .15rem;
            gap: .4rem;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .contact-directory-filters button {
            flex: 0 0 auto;
            padding: .4rem .72rem;
            border: 1px solid #e4dfd6;
            border-radius: 999px;
            background: #fff;
            color: #6b6256;
            font-size: .63rem;
            font-weight: 700;
            letter-spacing: .03em;
        }

        .contact-directory-filters button:hover,
        .contact-directory-filters button.is-active {
            border-color: #1c1915;
            background: #1c1915;
            color: #fff;
        }

        .contact-directory-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 1rem;
            gap: .65rem;
        }

        .contact-directory-card {
            position: relative;
            display: grid;
            min-width: 0;
            min-height: 132px;
            padding: .85rem 2rem .85rem .85rem;
            grid-template-columns: 38px minmax(0, 1fr);
            align-items: start;
            gap: .7rem;
            border: 1px solid #ebe6dd;
            border-radius: 12px;
            background: #fff;
            color: inherit;
            text-decoration: none;
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .contact-directory-card:hover,
        .contact-directory-card:focus-visible {
            border-color: #cdbb9e;
            color: inherit;
            box-shadow: 0 8px 20px rgba(28, 25, 21, .07);
            outline: none;
            transform: translateY(-2px);
        }

        .contact-directory-card[hidden] {
            display: none;
        }

        .contact-directory-icon {
            display: inline-grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 10px;
            background: linear-gradient(145deg, #f3eee5, #fbfaf8);
            color: #8b6a37;
            font-size: .88rem;
        }

        .contact-directory-copy,
        .contact-directory-copy small,
        .contact-directory-copy strong,
        .contact-directory-copy > span,
        .contact-directory-copy em {
            display: block;
            min-width: 0;
        }

        .contact-directory-copy small {
            margin-bottom: .2rem;
            overflow: hidden;
            color: #a18458;
            font-size: .55rem;
            font-weight: 800;
            letter-spacing: .09em;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .contact-directory-copy strong {
            overflow: hidden;
            color: #201d18;
            font-size: .78rem;
            font-weight: 750;
            line-height: 1.25;
            text-overflow: ellipsis;
        }

        .contact-directory-copy > span {
            display: -webkit-box;
            margin-top: .25rem;
            overflow: hidden;
            color: #787065;
            font-size: .64rem;
            line-height: 1.38;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .contact-directory-copy em {
            margin-top: .45rem;
            overflow: hidden;
            color: #28864b;
            font-size: .59rem;
            font-style: normal;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .contact-directory-copy em i {
            margin-right: .28rem;
        }

        .contact-directory-arrow {
            position: absolute;
            top: .85rem;
            right: .75rem;
            color: #bbb2a5;
            font-size: .58rem;
        }

        .contact-directory-empty {
            padding: 1.5rem;
            color: #81786c;
            font-size: .72rem;
            text-align: center;
        }

        .contact-directory-empty i {
            margin-right: .35rem;
        }

        .contact-card {
            border: 1px solid #ece7de;
            border-radius: 16px;
            background: #fff;
            padding: 1.25rem;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .06);
        }

        .contact-form-heading {
            display: flex;
            margin-bottom: 1.25rem;
            align-items: center;
            gap: .8rem;
        }

        .contact-form-heading__icon {
            display: grid;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            place-items: center;
            border-radius: 12px;
            background: #171512;
            color: #fff;
            font-size: .9rem;
        }

        .contact-form-heading span:not(.contact-form-heading__icon) {
            display: block;
            margin-bottom: .15rem;
            color: #9b7a45;
            font-size: .56rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .contact-form-heading h2 {
            margin: 0;
            color: #1b1814;
            font-size: 1rem;
            font-weight: 750;
        }

        .contact-form-heading p {
            margin: .2rem 0 0;
            color: #7b746a;
            font-size: .66rem;
        }

        .optical-prescription {
            display: flex;
            min-height: 118px;
            padding: 1rem;
            align-items: center;
            justify-content: center;
            gap: .8rem;
            border: 1px dashed #cfc5b6;
            border-radius: 10px;
            background: #faf9f7;
            color: #25211c;
            cursor: pointer;
            transition: border-color .18s ease, background .18s ease;
        }

        .optical-prescription:hover,
        .optical-prescription:focus-within {
            border-color: #8d7044;
            background: #f7f3ed;
        }

        .optical-prescription > i {
            color: #9b7a45;
            font-size: 1.1rem;
        }

        .optical-prescription strong,
        .optical-prescription small {
            display: block;
        }

        .optical-prescription strong {
            font-size: .7rem;
        }

        .optical-prescription small,
        .optical-prescription-name {
            color: #8a8277;
            font-size: .58rem;
        }

        .optical-prescription-name {
            display: block;
            margin-top: .4rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .contact-tabs {
            display: inline-flex;
            gap: 8px;
            background: #f5f2ed;
            border: 1px solid #e6e0d4;
            border-radius: 999px;
            padding: 4px;
            margin-bottom: 1.2rem;
        }

        .btn-sax-tab {
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #5f5648;
            font-size: .66rem;
            text-transform: uppercase;
            letter-spacing: .12em;
            font-weight: 700;
            padding: .52rem .95rem;
        }

        .btn-sax-tab.active {
            background: #161412;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .18);
        }

        .sax-label {
            display: block;
            margin-bottom: 8px;
            font-size: .63rem;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: #6a6153;
            font-weight: 700;
        }

        .sax-input {
            border: 1px solid #e5dfd4;
            border-radius: 12px;
            background: #fcfbf9;
            color: #1d1914;
            font-size: .88rem;
            padding: .72rem .9rem;
            box-shadow: none;
            transition: border-color .2s ease, background-color .2s ease;
        }

        .sax-input:focus {
            border-color: #c9bca8;
            background: #fff;
            box-shadow: none;
        }

        .btn-sax-submit {
            border: 1px solid #111;
            border-radius: 12px;
            background: #111;
            color: #fff;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .16em;
            font-weight: 800;
            padding: .95rem 1rem;
            transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
        }

        .btn-sax-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .16);
            color: #fff;
            opacity: .95;
        }

        .contact-grid {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
        }

        .contact-grid > form {
            flex: 1 1 66%;
            min-width: 0;
        }

        .contact-flyers {
            flex: 0 1 34%;
            max-width: 320px;
            min-width: 0;
        }

        .contact-flyers-head {
            margin-bottom: 1rem;
        }

        .contact-flyers-title {
            margin: 0;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .16em;
            font-weight: 700;
            color: #141311;
        }

        .contact-flyers-sub {
            margin: .4rem 0 0;
            font-size: .72rem;
            color: #7f786d;
        }

        .contact-flyer-frame {
            aspect-ratio: 9 / 16;
            border: 1px solid #ebe5db;
            border-radius: 14px;
            overflow: hidden;
            background: #f8f5ef;
        }

        .contact-flyer-frame img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .job-flyers-pagination.swiper-pagination {
            position: static;
            margin-top: 1rem;
        }

        .job-flyers-pagination .swiper-pagination-bullet {
            width: 7px;
            height: 7px;
            background: #141311;
            opacity: .22;
            transition: opacity .2s ease, transform .2s ease;
        }

        .job-flyers-pagination .swiper-pagination-bullet-active {
            opacity: 1;
            transform: scale(1.25);
        }

        .job-flyers-nav {
            display: flex;
            justify-content: center;
            gap: .6rem;
            margin-top: .6rem;
        }

        .job-flyers-btn {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5dfd4;
            border-radius: 50%;
            background: #fff;
            color: #141311;
            font-size: .68rem;
            transition: background-color .2s ease, border-color .2s ease, color .2s ease, opacity .2s ease;
        }

        .job-flyers-btn:hover {
            background: #161412;
            border-color: #161412;
            color: #fff;
        }

        .job-flyers-btn.swiper-button-disabled {
            opacity: .3;
            pointer-events: none;
        }

        .job-flyers-nav.is-hidden,
        .job-flyers-pagination.is-hidden {
            display: none;
        }

        .map-section-title {
            margin: 0;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: .16em;
            font-weight: 700;
            color: #141311;
        }

        .map-card {
            border: 1px solid #ece7de;
            border-radius: 14px;
            background: #fff;
            padding: 1rem;
            box-shadow: 0 8px 22px rgba(0, 0, 0, .04);
        }

        .map-title {
            margin: 0;
            font-size: .67rem;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: #1a1815;
            font-weight: 800;
        }

        .map-address {
            margin: .55rem 0 .9rem;
            color: #6f675b;
            font-size: .77rem;
            line-height: 1.55;
            min-height: 44px;
        }

        .map-wrapper {
            overflow: hidden;
            border-radius: 10px;
            border: 1px solid #ebe5db;
            background: #f8f5ef;
        }

        .map-wrapper iframe {
            display: block;
            width: 100%;
            height: 220px;
            border: 0;
        }

        @media (max-width: 991px) {
            .contact-card {
                padding: 1rem;
            }

            .contact-tabs {
                width: 100%;
                justify-content: space-between;
            }

            .contact-grid {
                flex-direction: column;
            }

            .contact-flyers {
                max-width: 280px;
                margin-inline: auto;
            }

            .contact-directory-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575px) {
            .contact-page-wrapper {
                padding-top: 2rem !important;
            }

            .contact-hero {
                margin-bottom: 2rem !important;
            }

            .contact-directory {
                padding: .8rem;
                border-radius: 13px;
            }

            .contact-directory-head {
                gap: .55rem;
            }

            .contact-directory-head h2 {
                font-size: .94rem;
            }

            .contact-directory-toggle {
                min-height: 34px;
                padding: .45rem .65rem;
                font-size: .56rem;
            }

            .contact-directory-content {
                padding-top: .75rem;
                margin-top: .75rem;
            }

            .contact-directory-tools {
                align-items: stretch;
                flex-direction: column;
            }

            .contact-directory-search {
                width: 100%;
                flex-basis: auto;
            }

            .contact-directory-search input {
                min-height: 36px;
                font-size: .66rem;
            }

            .contact-directory-filters {
                width: calc(100% + 1.6rem);
                margin-right: -.8rem;
                margin-left: -.8rem;
                padding-right: .8rem;
                padding-left: .8rem;
            }

            .contact-directory-filters button {
                padding: .34rem .6rem;
                font-size: .56rem;
            }

            .contact-directory-grid {
                grid-template-columns: 1fr;
                margin-top: .7rem;
                gap: .45rem;
            }

            .contact-directory-card {
                min-height: 0;
                padding: .65rem 1.7rem .65rem .65rem;
                grid-template-columns: 32px minmax(0, 1fr);
                gap: .55rem;
                border-radius: 10px;
            }

            .contact-directory-icon {
                width: 32px;
                height: 32px;
                border-radius: 8px;
                font-size: .72rem;
            }

            .contact-directory-copy small {
                font-size: .49rem;
            }

            .contact-directory-copy strong {
                font-size: .7rem;
            }

            .contact-directory-copy > span {
                font-size: .58rem;
                -webkit-line-clamp: 1;
            }

            .contact-directory-copy em {
                margin-top: .3rem;
                font-size: .54rem;
            }

            .contact-form-heading {
                margin-bottom: 1rem;
                gap: .65rem;
            }

            .contact-form-heading__icon {
                width: 36px;
                height: 36px;
                flex-basis: 36px;
                border-radius: 9px;
                font-size: .72rem;
            }

            .contact-form-heading h2 {
                font-size: .84rem;
            }

            .contact-form-heading p {
                font-size: .58rem;
            }

            .optical-prescription {
                min-height: 88px;
                padding: .75rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const directoryToggle = document.getElementById('contactDirectoryToggle');
            const directoryContent = document.getElementById('contactDirectoryContent');
            const directoryCards = Array.from(document.querySelectorAll('[data-contact-card]'));
            const directoryFilters = Array.from(document.querySelectorAll('[data-contact-filter]'));
            const directorySearch = document.getElementById('contactDirectorySearch');
            const directoryEmpty = document.getElementById('contactDirectoryEmpty');
            let activeDirectoryFilter = 'all';

            directoryToggle?.addEventListener('click', () => {
                const expanded = directoryToggle.getAttribute('aria-expanded') === 'true';
                directoryToggle.setAttribute('aria-expanded', String(!expanded));
                directoryContent.hidden = expanded;
                directoryToggle.querySelector('span').textContent = expanded
                    ? directoryToggle.dataset.showLabel
                    : directoryToggle.dataset.hideLabel;

                if (!expanded) {
                    applyDirectoryFilters();
                }
            });

            const normalizeDirectoryText = value => (value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();

            const applyDirectoryFilters = () => {
                const query = normalizeDirectoryText(directorySearch?.value);
                let visible = 0;

                directoryCards.forEach(card => {
                    const categoryMatches = activeDirectoryFilter === 'all'
                        || card.dataset.category === activeDirectoryFilter;
                    const searchMatches = !query
                        || normalizeDirectoryText(card.dataset.search).includes(query);
                    const show = categoryMatches && searchMatches;
                    card.hidden = !show;
                    if (show) visible++;
                });

                if (directoryEmpty) directoryEmpty.hidden = visible > 0;
            };

            directoryFilters.forEach(button => {
                button.addEventListener('click', () => {
                    activeDirectoryFilter = button.dataset.contactFilter;
                    directoryFilters.forEach(filter => filter.classList.toggle('is-active', filter === button));
                    applyDirectoryFilters();
                });
            });
            directorySearch?.addEventListener('input', applyDirectoryFilters);

            const opticalPrescription = document.querySelector('[data-optical-prescription]');
            const opticalPrescriptionName = document.querySelector('[data-optical-prescription-name]');
            opticalPrescription?.addEventListener('change', () => {
                opticalPrescriptionName.textContent = opticalPrescription.files?.[0]?.name
                    || opticalPrescriptionName.dataset.emptyLabel;
            });

            const flyersEl = document.querySelector('.jobFlyersSwiper');
            if (!flyersEl || typeof Swiper === 'undefined') return;

            const slideCount = flyersEl.querySelectorAll('.swiper-slide').length;
            const single = slideCount < 2;
            let initialized = false;

            window.afterFormTypeChange = function (type) {
                if (type !== 2 || initialized) return;
                initialized = true;

                if (single) {
                    flyersEl.querySelector('.job-flyers-nav')?.classList.add('is-hidden');
                    flyersEl.querySelector('.job-flyers-pagination')?.classList.add('is-hidden');
                }

                new Swiper(flyersEl, {
                    slidesPerView: 1,
                    spaceBetween: 16,
                    autoplay: single ? false : {
                        delay: 4500,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: flyersEl.querySelector('.job-flyers-pagination'),
                        clickable: true,
                    },
                    navigation: {
                        prevEl: flyersEl.querySelector('.job-flyers-prev'),
                        nextEl: flyersEl.querySelector('.job-flyers-next'),
                    },
                    loop: !single,
                });
            };
        });
    </script>
@endpush
