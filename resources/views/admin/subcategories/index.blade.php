@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="sax-cat">
        <x-admin.catalog-toolbar
            :title="__('messages.subcategorias_titulo')"
            :subtitle="__('messages.estrutura_de') . ' <strong>' . $subcategories->total() . '</strong> ' . __('messages.niveis_secundarios')"
            :createUrl="route('admin.subcategories.create')"
            :createLabel="__('messages.nova_subcategoria')"
            :searchAction="route('admin.subcategories.index')"
            :searchPlaceholder="__('messages.buscar_subcategoria_placeholder')" />

        <x-admin.alert />

        <div class="sax-cat__grid">
            @forelse ($subcategories as $subcategory)
                <x-admin.catalog-card
                    :id="$subcategory->id"
                    :title="$subcategory->name"
                    :meta="$subcategory->slug"
                    :parent="$subcategory->category->name ?? null"
                    :image="$subcategory->photo ? asset('storage/' . $subcategory->photo) : null"
                    :publicUrl="route('subcategories.show', $subcategory->slug)"
                    :editUrl="route('admin.subcategories.edit', $subcategory)"
                    :deleteUrl="route('admin.subcategories.destroy', $subcategory)"
                    :deleteConfirm="__('messages.confirmar_exclusao')" />
            @empty
                <p class="sax-cat__empty">{{ __('messages.nenhum_registro') }}</p>
            @endforelse
        </div>

        <div class="sax-cat__pag">{{ $subcategories->links() }}</div>
    </div>
</x-admin.card>
@endsection
