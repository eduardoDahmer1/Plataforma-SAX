@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.nueva_marca_titulo') }}"
        description="{{ __('messages.registro_firma_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.brands.index') }}" class="btn-back-minimal">
                <i class="fas fa-times me-1"></i> {{ __('messages.cancelar') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data" class="sax-form">
                @csrf

                <div class="row g-4">
                    {{-- Informações Textuais --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">{{ __('messages.datos_identificacion') }}</h6>

                            <div class="mb-4">
                                <label for="name" class="sax-form-label">{{ __('messages.nombre_marca_label') }}</label>
                                <input type="text" class="form-control sax-input @error('name') is-invalid @enderror"
                                    id="name" name="name" placeholder="Ej: Gucci" value="{{ old('name') }}"
                                    required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="sax-form-label">{{ __('messages.slug_navegacion_label') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 x-small fw-bold text-muted">/marcas/</span>
                                    <input type="text" class="form-control sax-input @error('slug') is-invalid @enderror"
                                        id="slug" name="slug" placeholder="gucci" value="{{ old('slug') }}" required>
                                </div>
                                <small class="text-muted x-small mt-2 d-block px-1">{{ __('messages.auto_slug_desc') }}</small>
                                @error('slug')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Upload de Arquivos --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">{{ __('messages.archivos_multimedia') }}</h6>

                            <div class="mb-4">
                                <label for="image" class="sax-form-label">{{ __('messages.logotipo_oficial_label') }}</label>
                                <div class="asset-upload-zone">
                                    <i class="fas fa-cloud-upload-alt mb-2 opacity-25"></i>
                                    <input type="file" class="form-control sax-input-file @error('image') is-invalid @enderror"
                                        id="image" name="image" accept="image/*">
                                    <p class="x-small text-muted mb-0">{{ __('messages.upload_logo_desc') }}</p>
                                </div>
                                @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="banner" class="sax-form-label">{{ __('messages.banner_promocional_label') }}</label>
                                <div class="asset-upload-zone">
                                    <i class="fas fa-image mb-2 opacity-25"></i>
                                    <input type="file" class="form-control sax-input-file @error('banner') is-invalid @enderror"
                                        id="banner" name="banner" accept="image/*">
                                    <p class="x-small text-muted mb-0">{{ __('messages.upload_banner_desc') }}</p>
                                </div>
                                @error('banner')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- NOVO CAMPO: Internal Banner --}}
                            <div class="mb-0">
                                <label for="internal_banner" class="sax-form-label">{{ __('messages.banner_interno_label') }}</label>
                                <div class="asset-upload-zone" style="border-color: #d1d5db; background: #f3f4f6;">
                                    <i class="fas fa-ad mb-2 opacity-25"></i>
                                    <input type="file" class="form-control sax-input-file @error('internal_banner') is-invalid @enderror"
                                        id="internal_banner" name="internal_banner" accept="image/*">
                                    <p class="x-small text-muted mb-0">{{ __('messages.banner_interno_desc') }}</p>
                                </div>
                                @error('internal_banner')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ações Inferiores --}}
                <x-admin.form-actions 
                    :cancelRoute="route('admin.brands.index')" 
                    cancelLabel="{{ __('messages.descartar_marca') }}" 
                    submitLabel="{{ __('messages.criar_marca_botao') }}" />
            </form>
        </div>
    </div>
</x-admin.card>
@endsection