@php $editando = isset($category); @endphp

<x-admin.catalog-form
    :title="$editando ? __('messages.editar_categoria_titulo') : __('messages.nova_categoria')"
    :action="$editando ? route('admin.categories.update', $category) : route('admin.categories.store')"
    :method="$editando ? 'PUT' : 'POST'"
    :backUrl="route('admin.categories.index')"
    :submitLabel="__('messages.salvar')">

    <h2 class="sax-catf__section">{{ __('messages.datos_identificacion') }}</h2>

    <div class="sax-catf__grid">
        <x-admin.field name="name" :label="__('messages.nome')"
                       :value="$category->name ?? null" :required="true" />

        <x-admin.field name="slug" :label="__('messages.slug_navegacion_label')"
                       :value="$category->slug ?? null" prefix="/categorias/"
                       :hint="__('messages.auto_slug_desc')" :required="true" />
    </div>

    <h2 class="sax-catf__section mt-4">{{ __('messages.archivos_multimedia') }}</h2>

    @if ($editando)
        <div class="sax-catf__grid">
            <x-admin.media-field field="photo" :label="__('messages.foto')"
                :current="$category->photo" :uploadUrl="route('admin.categories.uploadPhoto', $category->id)"
                :showDelete="true" ratio="square" dimensions="800 × 800 px" usage="Imagem de identificação nos atalhos do catálogo." />

            <x-admin.media-field field="banner" :label="__('messages.banner')"
                :current="$category->banner" :uploadUrl="route('admin.categories.uploadBanner', $category->id)"
                :showDelete="true" ratio="banner" dimensions="1920 × 560 px" usage="Banner de topo; mantenha textos na área central." />
        </div>
    @else
        <div class="sax-catf__grid">
            <x-admin.media-field field="photo" :label="__('messages.foto')" ratio="square"
                dimensions="800 × 800 px" usage="Imagem de identificação nos atalhos do catálogo." />
            <x-admin.media-field field="banner" :label="__('messages.banner')" ratio="banner"
                dimensions="1920 × 560 px" usage="Banner de topo; mantenha textos na área central." />
        </div>
    @endif
</x-admin.catalog-form>

@if ($editando)
    <form id="delete-photo-form" action="{{ route('admin.categories.deletePhoto', $category->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
    <form id="delete-banner-form" action="{{ route('admin.categories.deleteBanner', $category->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif
