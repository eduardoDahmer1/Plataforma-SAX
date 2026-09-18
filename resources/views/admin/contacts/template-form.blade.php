@extends('layout.admin')

@push('styles')
<link href="{{ asset('css/email-marketing.css') }}?v={{ filemtime(public_path('css/email-marketing.css')) }}" rel="stylesheet">
@endpush

@section('content')
<x-admin.card>
    <div class="email-center">
        <div class="email-center__top">
            <div>
                <a class="email-center__back" href="{{ route('admin.contatos.index', ['view' => 'templates']) }}"><i class="fa-solid fa-arrow-left"></i> Templates</a>
                <h1>{{ $template->exists ? 'Editar template' : 'Novo template' }}</h1>
                <p>Prepare um modelo reutilizável para seus próximos envios.</p>
            </div>
        </div>
        <x-admin.alert />
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <form method="POST" action="{{ $template->exists ? route('admin.email-templates.update', $template) : route('admin.email-templates.store') }}" class="email-compose-form" id="emailTemplateForm">
            @csrf
            @if($template->exists) @method('PUT') @endif
            <section class="email-form-card email-form-card--content">
                <label class="email-field"><span>Nome interno</span><input type="text" name="name" maxlength="120" required value="{{ old('name', $template->name) }}" placeholder="Ex.: Promoção de fim de semana"></label>
                <label class="email-field"><span>Assunto padrão</span><input type="text" name="subject" maxlength="255" required value="{{ old('subject', $template->subject) }}" placeholder="Assunto do e-mail"></label>
                <label class="email-field">
                    <span>Conteúdo</span>
                    <textarea id="editor-email" name="body" required data-upload-url="{{ route('admin.blogs.upload-image') }}">{{ old('body', $template->body) }}</textarea>
                    <small>Campos disponíveis: <code>@{{nome}}</code> e <code>@{{email}}</code>.</small>
                </label>
            </section>
            <div class="email-compose-actions">
                <a href="{{ route('admin.contatos.index', ['view' => 'templates']) }}" class="email-btn email-btn--ghost">Cancelar</a>
                <button class="email-btn email-btn--primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Salvar template</button>
            </div>
        </form>
    </div>
</x-admin.card>
@endsection
