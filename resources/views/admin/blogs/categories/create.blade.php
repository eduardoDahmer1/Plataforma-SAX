@extends('layout.admin')

@section('content')
<x-admin.card>
    <form action="{{ route('admin.blog-categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <x-admin.sticky-header
            :title="__('messages.nova_categoria_editorial')"
            :cancelRoute="route('admin.blog-categories.index')"
            :submitLabel="__('messages.crear_categoria_btn')" />

        <x-admin.alert />

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="d-flex flex-column gap-4">
                    <div class="sax-premium-card shadow-sm overflow-hidden">
                        <x-admin.block-header icon="fas fa-tag" number="01"
                            :title="__('messages.identificacion_categoria')"
                            :subtitle="__('messages.nome_visivel_leitores')" />

                        <div class="p-4">
                            <label for="name" class="sax-label">{{ __('messages.identificacion_categoria') }}</label>
                            <input type="text" name="name" id="name" class="form-control sax-input fw-bold fs-5"
                                   placeholder="{{ __('messages.placeholder_blog_cat') }}" value="{{ old('name') }}" required>
                        </div>
                    </div>

                    <div class="sax-premium-card shadow-sm overflow-hidden">
                        <x-admin.block-header icon="fas fa-image" number="02"
                            :title="__('messages.banner_cabecera')"
                            :subtitle="__('messages.formatos_recomendados')" />

                        <div class="p-4">
                            <x-admin.image-upload name="banner" previewId="categoryBannerPreview"
                                placeholder="https://placehold.co/800x300/1a1a1a/ffffff?text=Banner"
                                height="200px" maxSize="8MB" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna Lateral Informativa --}}
            <div class="col-lg-4">
                <div class="sax-premium-card shadow-sm p-4 bg-light">
                    <div class="icon-circle-gold mb-3"><i class="fas fa-lightbulb"></i></div>
                    <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3">{{ __('messages.recomendaciones_titulo') }}</h6>
                    <p class="x-small text-secondary lh-lg italic mb-0">
                        {{ __('messages.recomendaciones_blog_desc') }}
                    </p>
                </div>
            </div>
        </div>

        <x-admin.form-actions :cancelRoute="route('admin.blog-categories.index')" :submitLabel="__('messages.crear_categoria_btn')" />
    </form>
</x-admin.card>
@endsection
