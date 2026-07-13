@php
    $editando = isset($subcategory);
    $opcoesCategoria = $categories->pluck('name', 'id')->all();
@endphp

<x-admin.catalog-form
    :title="$editando ? __('messages.editar_subcategoria_titulo') : __('messages.nova_subcategoria')"
    :action="$editando ? route('admin.subcategories.update', $subcategory) : route('admin.subcategories.store')"
    :method="$editando ? 'PUT' : 'POST'"
    :backUrl="route('admin.subcategories.index')"
    :submitLabel="__('messages.salvar')">

    <h2 class="sax-catf__section">{{ __('messages.datos_identificacion') }}</h2>

    <div class="sax-catf__grid">
        <x-admin.field name="category_id" type="select" :label="__('messages.categoria')"
                       :value="$subcategory->category_id ?? null" :options="$opcoesCategoria" :required="true" />

        <x-admin.field name="name" :label="__('messages.nome')"
                       :value="$subcategory->name ?? null" :required="true"
                       :hint="__('messages.slug_gerado_do_nome')" />

        @if ($editando)
            {{-- O slug é gerado do nome pelo controller; aqui é só informativo --}}
            <div class="sax-field">
                <label class="sax-field__label">{{ __('messages.slug_navegacion_label') }}</label>
                <input type="text" class="sax-field__control" value="/subcategorias/{{ $subcategory->slug }}" disabled>
            </div>
        @endif
    </div>

    <h2 class="sax-catf__section mt-4">{{ __('messages.archivos_multimedia') }}</h2>

    @if ($editando)
        <div class="sax-catf__grid">
            <x-admin.media-field field="photo" :label="__('messages.foto')"
                :current="$subcategory->photo" :uploadUrl="route('admin.subcategories.uploadPhoto', $subcategory->id)"
                :showDelete="true" ratio="square" />

            <x-admin.media-field field="banner" :label="__('messages.banner')"
                :current="$subcategory->banner" :uploadUrl="route('admin.subcategories.uploadBanner', $subcategory->id)"
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
    <form id="delete-photo-form" action="{{ route('admin.subcategories.deletePhoto', $subcategory->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
    <form id="delete-banner-form" action="{{ route('admin.subcategories.deleteBanner', $subcategory->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif
