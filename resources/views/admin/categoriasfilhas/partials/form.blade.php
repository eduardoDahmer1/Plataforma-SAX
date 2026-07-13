@php
    // No edit o controller envia o model em $categoriasfilhas (mesmo nome usado na listagem)
    $filha = $categoriasfilhas ?? null;
    $editando = $filha !== null;
    $opcoesSub = $subcategories->pluck('name', 'id')->all();
@endphp

<x-admin.catalog-form
    :title="$editando ? __('messages.editar_categoria_filha_titulo') : __('messages.nova_sub_subcategoria')"
    :action="$editando ? route('admin.categorias-filhas.update', $filha->id) : route('admin.categorias-filhas.store')"
    :method="$editando ? 'PUT' : 'POST'"
    :backUrl="route('admin.categorias-filhas.index')"
    :submitLabel="__('messages.salvar')">

    <h2 class="sax-catf__section">{{ __('messages.datos_identificacion') }}</h2>

    <div class="sax-catf__grid">
        <x-admin.field name="subcategory_id" type="select" :label="__('messages.subcategoria')"
                       :value="$filha->subcategory_id ?? null" :options="$opcoesSub" :required="true" />

        <x-admin.field name="name" :label="__('messages.nome')"
                       :value="$filha->name ?? null" :required="true"
                       :hint="__('messages.slug_gerado_do_nome')" />

        @if ($editando)
            {{-- O slug é gerado do nome pelo controller; aqui é só informativo --}}
            <div class="sax-field">
                <label class="sax-field__label">{{ __('messages.slug_navegacion_label') }}</label>
                <input type="text" class="sax-field__control" value="/categorias-filhas/{{ $filha->slug }}" disabled>
            </div>
        @endif
    </div>

    <h2 class="sax-catf__section mt-4">{{ __('messages.archivos_multimedia') }}</h2>

    @if ($editando)
        <div class="sax-catf__grid">
            <x-admin.media-field field="photo" :label="__('messages.foto')"
                :current="$filha->photo" :uploadUrl="route('admin.categorias-filhas.uploadPhoto', $filha->id)"
                :showDelete="true" ratio="square" />

            <x-admin.media-field field="banner" :label="__('messages.banner')"
                :current="$filha->banner" :uploadUrl="route('admin.categorias-filhas.uploadBanner', $filha->id)"
                :showDelete="true" ratio="banner" />
        </div>
    @else
        <div class="sax-catf__grid">
            <x-admin.field name="photo" type="file" :label="__('messages.foto')" :hint="__('messages.upload_logo_desc')" />
            <x-admin.field name="banner" type="file" :label="__('messages.banner')" :hint="__('messages.upload_banner_desc')" />
        </div>
    @endif
</x-admin.catalog-form>

@if ($editando)
    <form id="delete-photo-form" action="{{ route('admin.categorias-filhas.deletePhoto', $filha->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
    <form id="delete-banner-form" action="{{ route('admin.categorias-filhas.deleteBanner', $filha->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif
