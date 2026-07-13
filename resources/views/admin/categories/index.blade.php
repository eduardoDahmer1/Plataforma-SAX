@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="sax-cat">
        <x-admin.catalog-toolbar
            :title="__('messages.categorias_titulo')"
            :subtitle="__('messages.catalogo_com') . ' <strong>' . $categories->total() . '</strong> ' . __('messages.departamentos_ativos')"
            :createUrl="route('admin.categories.create')"
            :createLabel="__('messages.nova_categoria')"
            :searchAction="route('admin.categories.index')"
            :searchPlaceholder="__('messages.buscar_categoria_placeholder')" />

        <x-admin.alert />

        <div class="sax-cat__grid">
            @forelse ($categories as $category)
                <x-admin.catalog-card
                    :id="$category->id"
                    :title="$category->name"
                    :meta="$category->slug"
                    :image="$category->photo ? asset('storage/' . $category->photo) : null"
                    :publicUrl="route('categories.show', $category->slug)"
                    :editUrl="route('admin.categories.edit', $category)"
                    :deleteUrl="route('admin.categories.destroy', $category)"
                    :deleteConfirm="__('messages.confirmar_exclusao')" />
            @empty
                <p class="sax-cat__empty">{{ __('messages.nenhum_registro') }}</p>
            @endforelse
        </div>

        <div class="sax-cat__pag">{{ $categories->links() }}</div>
    </div>
</x-admin.card>
@endsection
