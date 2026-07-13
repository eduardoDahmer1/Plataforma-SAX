@php $editando = isset($brand); @endphp

<x-admin.catalog-form
    :title="$editando ? __('messages.editar_marca_titulo') : __('messages.nueva_marca_titulo')"
    :subtitle="__('messages.registro_firma_desc')"
    :action="$editando ? route('admin.brands.update', $brand) : route('admin.brands.store')"
    :method="$editando ? 'PUT' : 'POST'"
    :backUrl="route('admin.brands.index')"
    :submitLabel="$editando ? __('messages.salvar') : __('messages.criar_marca_botao')">

    <h2 class="sax-catf__section">{{ __('messages.datos_identificacion') }}</h2>

    <div class="sax-catf__grid">
        <x-admin.field name="name" :label="__('messages.nombre_marca_label')"
                       :value="$brand->name ?? null" placeholder="Ex: Gucci" :required="true" />

        <x-admin.field name="slug" :label="__('messages.slug_navegacion_label')"
                       :value="$brand->slug ?? null" prefix="/marcas/" placeholder="gucci"
                       :hint="__('messages.auto_slug_desc')" :required="true" />
    </div>

    <h2 class="sax-catf__section mt-4">{{ __('messages.archivos_multimedia') }}</h2>

    @if ($editando)
        {{-- Na edição as imagens já sobem por AJAX (media-field), com preview e remoção --}}
        <div class="sax-catf__grid">
            <x-admin.media-field field="image" :label="__('messages.logotipo_oficial_label')"
                :current="$brand->image" :uploadUrl="route('admin.brands.uploadLogo', $brand->id)"
                :showDelete="true" ratio="square" />

            <x-admin.media-field field="banner" :label="__('messages.banner_promocional_label')"
                :current="$brand->banner" :uploadUrl="route('admin.brands.uploadBanner', $brand->id)"
                :showDelete="true" ratio="banner" />

            <x-admin.media-field field="internal_banner" :label="__('messages.banner_interno_label')"
                :current="$brand->internal_banner" :uploadUrl="route('admin.brands.uploadInternalBanner', $brand->id)"
                :showDelete="true" ratio="banner" />
        </div>
    @else
        <div class="sax-catf__grid">
            <x-admin.field name="image" type="file" :label="__('messages.logotipo_oficial_label')" :hint="__('messages.upload_logo_desc')" />
            <x-admin.field name="banner" type="file" :label="__('messages.banner_promocional_label')" :hint="__('messages.upload_banner_desc')" />
            <x-admin.field name="internal_banner" type="file" :label="__('messages.banner_interno_label')" :hint="__('messages.banner_interno_desc')" />
        </div>
    @endif
</x-admin.catalog-form>

@if ($editando)
    {{-- Formulários usados pelos botões de remover imagem do media-field --}}
    <form id="delete-image-form" action="{{ route('admin.brands.deleteLogo', $brand->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
    <form id="delete-banner-form" action="{{ route('admin.brands.deleteBanner', $brand->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
    <form id="delete-internal_banner-form" action="{{ route('admin.brands.deleteInternalBanner', $brand->id) }}" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endif
