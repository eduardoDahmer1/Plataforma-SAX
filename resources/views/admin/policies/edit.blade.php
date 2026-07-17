@extends('layout.admin')

@section('content')
<x-admin.card>
    <form action="{{ route('admin.policies.update', $policy) }}" method="POST">
        @csrf
        @method('PUT')

        <x-admin.sticky-header title="Editar política" :cancelRoute="route('admin.policies.index')" submitLabel="Salvar política" :updatedAt="'Última atualização: ' . $policy->updated_at->format('d/m/Y H:i')" />
        <x-admin.alert />

        <div class="sax-premium-card shadow-sm overflow-hidden mt-4">
            <x-admin.block-header icon="fas fa-scale-balanced" number="01" title="Conteúdo da política" subtitle="Este texto será exibido publicamente e vinculado no checkout." />
            <div class="p-4">
                <label for="title" class="sax-label">Título</label>
                <input id="title" name="title" class="form-control sax-input fw-bold mb-4" value="{{ old('title', $policy->title) }}" required>

                <label for="editor-blog" class="sax-label">Texto</label>
                <div class="editor-rich-wrapper">
                    <textarea id="editor-blog" name="content" required
                              data-upload-url="{{ route('admin.blogs.upload-image') }}">{{ old('content', $policy->content) }}</textarea>
                </div>

                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $policy->is_active))>
                    <label class="form-check-label fw-bold" for="is_active">Exibir esta política no site</label>
                </div>
            </div>
        </div>

        <x-admin.form-actions :cancelRoute="route('admin.policies.index')" submitLabel="Salvar política" />
    </form>
</x-admin.card>
@endsection
