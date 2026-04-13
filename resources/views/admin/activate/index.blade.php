@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header 
        title="{{ __('messages.categoria_blog_detalhes') }}" 
        description="{{ __('messages.detalhes_recurso_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.blog-categories.index') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> {{ __('messages.voltar_listado_btn') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="sax-premium-card border-0 shadow-sm overflow-hidden">
                {{-- Preview do Banner --}}
                <div class="banner-preview-wrapper bg-light">
                    @if($category->banner && file_exists(storage_path('app/public/' . $category->banner)))
                        <img src="{{ asset('storage/' . $category->banner) }}" class="banner-display-img">
                        <div class="banner-overlay">
                            <span class="badge bg-white text-dark rounded-pill px-3 shadow-sm">{{ __('messages.vista_previa') }}</span>
                        </div>
                    @else
                        <div class="banner-empty-state py-5 text-center">
                            <i class="far fa-image fa-3x mb-3 opacity-25"></i>
                            <p class="text-muted text-uppercase letter-spacing-1 small mb-0">{{ __('messages.sem_banner_atribuido') }}</p>
                        </div>
                    @endif
                </div>

                {{-- Informações Técnicas --}}
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <label class="sax-label mb-1">{{ __('messages.nome_categoria_cap') }}</label>
                            <h3 class="fw-bold text-dark mb-0">{{ $category->name }}</h3>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <span class="badge bg-soft-dark text-dark border px-3 py-2">
                                ID: #{{ $category->id ?? '0' }}
                            </span>
                        </div>
                    </div>

                    <hr class="my-4 opacity-10">

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('admin.blog-categories.edit', $category->id) }}" class="btn btn-dark rounded-pill px-4 btn-sm fw-bold">
                            <i class="fas fa-edit me-2"></i> {{ __('messages.editar_categoria_btn_cap') }}
                        </a>
                        {{-- Link para ver no blog (Exemplo de slug se existir) --}}
                        <a href="#" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">
                            <i class="fas fa-external-link-alt me-2"></i> {{ __('messages.ver_no_blog_btn') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin.card>
@endsection