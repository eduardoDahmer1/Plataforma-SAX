@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.nova_subcategoria_titulo') }}"
        description="{{ __('messages.definir_nivel_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.subcategories.index') }}" class="btn-back-minimal">
                <i class="fas fa-times me-1"></i> {{ __('messages.descartar') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form action="{{ route('admin.subcategories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    
                    {{-- Coluna 1: Posicionamento e Nome --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">{{ __('messages.ubicacion_nombre') }}</h6>
                            
                            <div class="mb-4">
                                <label class="sax-form-label">{{ __('messages.nome_subcategoria_label') }}</label>
                                <input type="text" name="name" id="name" class="form-control sax-input" 
                                       placeholder="Ej: Calzado Deportivo" value="{{ old('name') }}" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label class="sax-form-label">{{ __('messages.vincular_categoria_label') }}</label>
                                <div class="sax-select-wrapper">
                                    <select name="category_id" class="form-select sax-input" required>
                                        <option value="" disabled selected>{{ __('messages.selecione_origem') }}</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name ?: $category->slug }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down select-icon"></i>
                                </div>
                                <small class="text-muted x-small mt-2 d-block px-1">
                                    <i class="fas fa-info-circle me-1"></i> {{ __('messages.info_menu_desc') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Coluna 2: Recursos Visuais --}}
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">{{ __('messages.imagens_apoio') }}</h6>

                            <div class="mb-4">
                                <label class="sax-form-label">{{ __('messages.imagem_ícone_label') }}</label>
                                <div class="sax-upload-area">
                                    <i class="fas fa-upload mb-2 opacity-25"></i>
                                    <input type="file" name="photo" class="sax-file-hidden">
                                    <p class="x-small text-muted mb-0">{{ __('messages.subir_foto_quadrada') }}</p>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="sax-form-label">{{ __('messages.banner_subsecao_label') }}</label>
                                <div class="sax-upload-area">
                                    <i class="fas fa-images mb-2 opacity-25"></i>
                                    <input type="file" name="banner" class="sax-file-hidden">
                                    <p class="x-small text-muted mb-0">{{ __('messages.sugerido_medida') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer de Ações --}}
                <x-admin.form-actions 
                    :cancelRoute="route('admin.subcategories.index')" 
                    cancelLabel="{{ __('messages.voltar') }}" 
                    submitLabel="{{ __('messages.criar_subnivel') }}" />
            </form>
        </div>
    </div>
</x-admin.card>
@endsection