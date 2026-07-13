@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="sax-cat">
        <x-admin.catalog-toolbar
            :title="__('messages.categorias_filhas_titulo')"
            :subtitle="__('messages.gerenciando_terminais_desc', ['total' => $categoriasfilhas->total()])"
            :createUrl="route('admin.categorias-filhas.create')"
            :createLabel="__('messages.nova_sub_subcategoria')"
            :searchAction="route('admin.categorias-filhas.index')"
            :searchPlaceholder="__('messages.buscar_terminal_placeholder')" />

        <x-admin.alert />

        <div class="sax-cat__grid">
            @forelse ($categoriasfilhas as $filha)
                <x-admin.catalog-card
                    :id="$filha->id"
                    :title="$filha->name"
                    :meta="$filha->slug"
                    :parent="$filha->subcategory->name ?? null"
                    :image="$filha->photo ? asset('storage/' . $filha->photo) : null"
                    :publicUrl="route('categorias-filhas.show', $filha->slug)"
                    :editUrl="route('admin.categorias-filhas.edit', $filha->id)"
                    :deleteUrl="route('admin.categorias-filhas.destroy', $filha->id)"
                    :deleteConfirm="__('messages.confirmar_exclusao')" />
            @empty
                <p class="sax-cat__empty">{{ __('messages.nenhum_registro') }}</p>
            @endforelse
        </div>

        <div class="sax-cat__pag">{{ $categoriasfilhas->links() }}</div>
    </div>
</x-admin.card>
@endsection
