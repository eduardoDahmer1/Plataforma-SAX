@extends('layout.layout')

@section('content')
    <div class="contact-page-wrapper py-5">
        <div class="container">
            <div class="contact-hero text-center mb-5">
                <h1 class="contact-title">{{ __('messages.contato') }}</h1>
                <p class="contact-subtitle">{{ __('messages.estamos_disposicao') }}</p>
            </div>

            @if(session('success'))
                <div class="alert alert-dark border-0 contact-alert d-flex align-items-center mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="contact-card mb-5">
                <div class="contact-tabs" role="tablist" aria-label="Tipo de contato">
                    <button type="button" class="btn btn-sax-tab active" id="btn-atendimento" onclick="setFormType(1)">
                        {{ __('messages.atendimento') }}
                    </button>
                    <button type="button" class="btn btn-sax-tab" id="btn-curriculo" onclick="setFormType(2)">
                        {{ __('messages.trabalhe_conosco') }}
                    </button>
                </div>

                <form action="{{ route('contact.store') }}" method="POST" id="contactForm" enctype="multipart/form-data" class="sax-form">
                    @csrf
                    <input type="hidden" name="contact_type" id="contact_type" value="1">

                    <div class="row g-3 g-md-4">
                        <div class="col-md-4">
                            <label class="sax-label">{{ __('messages.nome_completo') }}</label>
                            <input type="text" name="name" class="form-control sax-input" placeholder="Ex: Maria Silva" required>
                        </div>

                        <div class="col-md-4">
                            <label class="sax-label">{{ __('messages.email') }}</label>
                            <input type="email" name="email" class="form-control sax-input" placeholder="email@exemplo.com" required>
                        </div>

                        <div class="col-md-4">
                            <label class="sax-label">{{ __('messages.telefone') }}</label>
                            <input type="text" name="phone" class="form-control sax-input" placeholder="+595 XXX XXXXXX">
                        </div>

                        <div class="col-md-12 form-field" data-type="1">
                            <label class="sax-label">{{ __('messages.mensagem') }}</label>
                            <textarea name="message" class="form-control sax-input" rows="5" placeholder="{{ __('messages.como_ajudar') }}" required></textarea>
                        </div>

                        <div class="col-md-12 form-field" data-type="2" style="display:none;">
                            <label class="sax-label">{{ __('messages.anexar_curriculo') ?? 'ANEXAR CURRÍCULO (PDF/IMG)' }}</label>
                            <input type="file" name="attachment" class="form-control sax-input" accept=".pdf,image/*">
                        </div>

                        <div class="col-md-4 ms-auto">
                            <button type="submit" class="btn btn-sax-submit w-100">
                                {{ __('messages.enviar') }}
                            </button>
                        </div>
                    </div>
                </form>
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

        .contact-alert {
            border-radius: 12px;
            background: #121212;
            color: #fff;
            padding: 14px 16px;
        }

        .contact-card {
            border: 1px solid #ece7de;
            border-radius: 16px;
            background: #fff;
            padding: 1.25rem;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .06);
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
        }
    </style>
@endpush