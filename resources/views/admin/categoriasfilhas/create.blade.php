@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.nova_categoriafilha_titulo') }}"  
        description="{{ __('messages.definir_departamento_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.categorias-filhas.index') }}" class="btn-back-minimal">
                <i class="fas fa-times me-1"></i> {{ __('messages.voltar_seta') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <form action="{{ route('admin.categorias-filhas.store') }}" method="POST" enctype="multipart/form-data" class="sax-form">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">{{ __('messages.ubicacion_nombre') }}</h6>

                            <div class="mb-4">
                                <label for="name" class="sax-form-label">{{ __('messages.nome_subcategoria_label') }}</label>
                                <input type="text" name="name" id="name" class="form-control sax-input @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="subcategory_id" class="sax-form-label">{{ __('messages.vincular_categoria_label') }}</label>
                                <div class="sax-select-wrapper">
                                    <select name="subcategory_id" id="subcategory_id" class="form-select sax-input @error('subcategory_id') is-invalid @enderror" required>
                                        <option value="" disabled {{ old('subcategory_id') ? '' : 'selected' }}>{{ __('messages.selecione_origem') }}</option>
                                        @foreach ($subcategories as $subcategory)
                                            <option value="{{ $subcategory->id }}" {{ old('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                                {{ $subcategory->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-sitemap select-icon opacity-50"></i>
                                </div>
                                @error('subcategory_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="sax-premium-card p-4 h-100 shadow-sm border-0">
                            <h6 class="sax-label mb-4 text-dark border-bottom pb-2">{{ __('messages.imagens_apoio') }}</h6>

                            <div class="mb-4">
                                <label for="photo" class="sax-form-label">{{ __('messages.imagem_ícone_label') }}</label>
                                <div class="sax-file-dropzone">
                                    <i class="fas fa-camera mb-2 opacity-25"></i>
                                    <input type="file" name="photo" id="photo" class="form-control sax-file-input @error('photo') is-invalid @enderror" accept="image/*">
                                    <p class="x-small text-muted mb-0">{{ __('messages.subir_foto_quadrada') }}</p>
                                </div>
                                @error('photo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="banner" class="sax-form-label">{{ __('messages.banner_subsecao_label') }}</label>
                                <div class="sax-file-dropzone">
                                    <i class="fas fa-images mb-2 opacity-25"></i>
                                    <input type="file" name="banner" id="banner" class="form-control sax-file-input @error('banner') is-invalid @enderror" accept="image/*">
                                    <p class="x-small text-muted mb-0">{{ __('messages.sugerido_medida') }}</p>
                                </div>
                                @error('banner')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <x-admin.form-actions
                    :cancelRoute="route('admin.categorias-filhas.index')"
                    cancelLabel="{{ __('messages.voltar') }}" 
                    submitLabel="{{ __('messages.criar_subnivel') }}"    />
            </form>
        </div>
    </div>
</x-admin.card>
@endsection
