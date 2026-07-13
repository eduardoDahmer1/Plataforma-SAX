@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="sax-cat">
        <x-admin.catalog-toolbar
            :title="__('messages.marcas_titulo')"
            :subtitle="__('messages.mostrando_marcas') . ' <strong>' . $brands->count() . '</strong> ' . __('messages.de') . ' ' . $brands->total() . ' ' . __('messages.marcas_cadastradas')"
            :createUrl="route('admin.brands.create')"
            :createLabel="__('messages.nova_marca')"
            :searchAction="route('admin.brands.index')"
            :searchPlaceholder="__('messages.buscar_marca_placeholder')" />

        <x-admin.alert />

        <div class="sax-cat__grid">
            @forelse ($brands as $brand)
                <x-admin.catalog-card
                    :id="$brand->id"
                    :title="$brand->name"
                    :meta="$brand->slug"
                    :image="$brand->image ? asset('storage/' . $brand->image) : null"
                    :publicUrl="route('brands.show', $brand->slug)"
                    :editUrl="route('admin.brands.edit', $brand)"
                    :deleteUrl="route('admin.brands.destroy', $brand)"
                    :deleteConfirm="__('messages.confirmar_eliminar_marca')" />
            @empty
                <p class="sax-cat__empty">{{ __('messages.nenhum_registro') }}</p>
            @endforelse
        </div>

        <div class="sax-cat__pag">{{ $brands->links() }}</div>
    </div>
</x-admin.card>
@endsection
