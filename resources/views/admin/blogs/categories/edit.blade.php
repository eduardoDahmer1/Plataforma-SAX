@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header 
        title="{{ __('messages.editar_categoria_blog_titulo') }}" 
        description="{{ __('messages.atualize_categoria_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.blog-categories.index') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> {{ __('messages.voltar_listado_btn') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form action="{{ route('admin.blog-categories.update', $category) }}" method="POST" enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="sax-label">{{ __('messages.nome_categoria_label') }}</label>
            <input type="text" name="name" class="form-control sax-input" id="name" 
                   value="{{ old('name', $category->name) }}" required>
        </div>

        <div class="mb-4">
            <label for="banner" class="sax-label">{{ __('messages.banner_label') }}</label>
            <div class="sax-upload-container p-3 border">
                <input type="file" name="banner" class="form-control mb-2 rounded-0 shadow-none" id="banner" accept="image/*">
                
                @if($category->banner)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $category->banner) }}" class="img-fluid rounded border" style="max-height:200px;">
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-5 border-top pt-4">
            <x-admin.form-actions 
                :cancelRoute="route('admin.blog-categories.index')" 
                submitLabel="{{ __('messages.salvar_alteracoes_btn') }}" />
        </div>
    </form>
</x-admin.card>
@endsection