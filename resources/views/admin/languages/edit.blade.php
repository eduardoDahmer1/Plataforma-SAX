@extends('layout.admin')
@section('content')

<div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
    <div class="d-flex align-items-center mb-4">
        <div style="width: 4px; height: 20px; background-color: #000; margin-right: 10px;"></div>
        <h5 class="m-0" style="font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
            {{ isset($language) ? __('messages.editar_traducao') : __('messages.nova_traducao') }}
        </h5>
    </div>

    <form action="{{ isset($language) ? route('admin.languages.update', $language->id) : route('admin.languages.store') }}" method="POST">
        @csrf
        @if(isset($language)) @method('PUT') @endif

        <div class="row">
            <div class="col-12 mb-4">
                <div class="sax-admin-form-group">
                    <label>{{ __('messages.label_chave') }} (EX: BOTAO_VENDER)</label>
                    <input type="text" name="key" class="form-control sax-admin-input" 
                           placeholder="{{ __('messages.placeholder_chave') }}"
                           value="{{ $language->key ?? '' }}" 
                           {{ isset($language) ? 'readonly' : '' }} required>
                    @if(isset($language))
                        <small class="text-muted">{{ __('messages.aviso_chave_readonly') }}</small>
                    @endif
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="sax-admin-form-group">
                    <label>{{ __('messages.portugues_label') }} (PT)</label>
                    <textarea name="pt" class="form-control sax-admin-input" rows="4" required>{{ $language->pt ?? '' }}</textarea>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="sax-admin-form-group">
                    <label>{{ __('messages.ingles_label') }} (EN)</label>
                    <textarea name="en" class="form-control sax-admin-input" rows="4" required>{{ $language->en ?? '' }}</textarea>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="sax-admin-form-group">
                    <label>{{ __('messages.espanhol_label') }} (ES)</label>
                    <textarea name="es" class="form-control sax-admin-input" rows="4" required>{{ $language->es ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <x-admin.form-actions 
            :cancelRoute="route('admin.languages.index')" 
            cancelLabel="{{ __('messages.voltar') }}" 
            submitLabel="{{ __('messages.salvar_traducoes') }}" />

    </form>
</div>

@endsection