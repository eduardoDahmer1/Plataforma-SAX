@extends('layout.admin')

@push('styles')
<link href="{{ asset('css/email-marketing.css') }}?v={{ filemtime(public_path('css/email-marketing.css')) }}" rel="stylesheet">
@endpush

@section('content')
<x-admin.card>
    <div class="email-center">
        <div class="email-center__top">
            <div>
                <a class="email-center__back" href="{{ route('admin.contatos.index') }}"><i class="fa-solid fa-arrow-left"></i> Central de mensagens</a>
                <h1>Novo e-mail</h1>
                <p>Crie campanhas, divulgue produtos e cupons ou responda a um contato.</p>
            </div>
        </div>

        <x-admin.alert />
        @if ($errors->any())
            <div class="alert alert-danger"><strong>Revise os campos:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        @if($replyContact)
            <div class="email-reply-context">
                <i class="fa-solid fa-reply"></i>
                <div><strong>Respondendo a {{ $replyContact->name }}</strong><span>{{ $replyContact->email }}</span><p>{{ Str::limit($replyContact->message, 240) }}</p></div>
            </div>
        @endif
        @if($selectedContacts->isNotEmpty())
            <div class="email-reply-context">
                <i class="fa-solid fa-users"></i>
                <div><strong>{{ $selectedContacts->count() }} contato(s) selecionado(s)</strong><span>{{ $selectedContacts->pluck('email')->join(', ') }}</span></div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.emails.send') }}" id="emailCampaignForm" class="email-compose-form">
            @csrf
            <div class="email-form-grid">
                <section class="email-form-card">
                    <div class="email-form-card__title"><span>1</span><div><strong>Destinatários</strong><small>Escolha o público deste envio</small></div></div>
                    <label class="email-field">
                        <span>Público</span>
                        <select name="audience" id="emailAudience" required>
                            @foreach($audiences as $value => $label)
                                @if(($value !== 'reply' || $replyContact) && ($value !== 'selected_contacts' || $selectedContacts->isNotEmpty()))
                                    <option value="{{ $value }}" @selected(old('audience', $replyContact ? 'reply' : ($selectedContacts->isNotEmpty() ? 'selected_contacts' : 'customers')) === $value)>
                                        {{ $label }}@if(isset($audienceCounts[$value])) ({{ number_format($audienceCounts[$value], 0, ',', '.') }})@endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </label>
                    <input type="hidden" name="contact_id" value="{{ old('contact_id', $replyContact?->id) }}">
                    @foreach($selectedContacts as $selectedContact)
                        <input type="hidden" name="contact_ids[]" value="{{ $selectedContact->id }}">
                    @endforeach
                    <label class="email-field" id="specificEmailsField" hidden>
                        <span>E-mails específicos</span>
                        <textarea name="specific_emails" rows="4" placeholder="cliente@exemplo.com, outro@exemplo.com">{{ old('specific_emails') }}</textarea>
                        <small>Separe os endereços por vírgula, espaço ou uma linha por e-mail. Máximo de 500.</small>
                    </label>
                    <p class="email-audience-note"><i class="fa-solid fa-shield-heart"></i> Endereços que cancelaram campanhas promocionais são removidos automaticamente.</p>
                </section>

                <section class="email-form-card">
                    <div class="email-form-card__title"><span>2</span><div><strong>Template</strong><small>Comece de um modelo salvo ou do zero</small></div></div>
                    <label class="email-field">
                        <span>Usar template</span>
                        <select name="template_id" id="emailTemplateSelect">
                            <option value="">Criar do zero</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" @selected((string) old('template_id', request('template')) === (string) $template->id)>{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="email-save-template">
                        <input type="checkbox" name="save_template" value="1" id="saveAsTemplate" @checked(old('save_template'))>
                        <span>Salvar este conteúdo como um novo template</span>
                    </label>
                    <label class="email-field" id="templateNameField" hidden>
                        <span>Nome do novo template</span>
                        <input type="text" name="template_name" value="{{ old('template_name') }}" maxlength="120" placeholder="Ex.: Lançamento de coleção">
                    </label>
                </section>
            </div>

            <section class="email-form-card email-form-card--content">
                <div class="email-form-card__title"><span>3</span><div><strong>Mensagem</strong><small>Você pode alterar livremente o template antes de enviar</small></div></div>
                <label class="email-field">
                    <span>Assunto</span>
                    <input type="text" id="emailSubject" name="subject" maxlength="255" required value="{{ old('subject', $selectedTemplate?->subject ?? ($replyContact ? 'Re: '.Str::limit((string) $replyContact->message, 90) : '')) }}" placeholder="Assunto que aparecerá na caixa de entrada">
                </label>
                <label class="email-field">
                    <span>Conteúdo do e-mail</span>
                    <textarea id="editor-email" name="body" required data-upload-url="{{ route('admin.blogs.upload-image') }}">{{ old('body', $selectedTemplate?->body) }}</textarea>
                    <small>Use <code>@{{nome}}</code> ou <code>@{{email}}</code> para personalizar cada mensagem.</small>
                </label>
            </section>

            <div class="email-compose-actions">
                <a href="{{ route('admin.contatos.index') }}" class="email-btn email-btn--ghost">Cancelar</a>
                <button type="submit" class="email-btn email-btn--primary" id="sendCampaignButton"><i class="fa-solid fa-paper-plane"></i> Preparar e enviar</button>
            </div>
        </form>
    </div>
</x-admin.card>

<script type="application/json" id="emailTemplatesData">{!! $templates->mapWithKeys(fn($template) => [$template->id => ['subject' => $template->subject, 'body' => $template->body]])->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
