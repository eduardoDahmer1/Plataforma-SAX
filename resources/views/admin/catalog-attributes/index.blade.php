@extends('layout.admin')
@section('content')
<x-admin.card>
    <div id="catalogAttributeAdmin" data-base-url="{{ route('admin.catalog-attributes.index') }}" data-translate-url="{{ route('admin.catalog-attributes.translate') }}">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3"><div><h1 class="h4">Atributos de productos</h1><p class="text-muted mb-0">Diccionario global disponible para todos los productos.</p></div><button type="button" class="btn btn-primary" data-create-attribute>Crear atributo</button></div>
        <input class="form-control mb-3" data-dictionary-search placeholder="Buscar atributo o código" aria-label="Buscar atributo">
        <div data-dictionary-list></div>
        <script type="application/json" data-attribute-dictionary>@json($dictionary)</script>
    </div>
    @include('admin.catalog-attributes.modal')
</x-admin.card>
<script src="{{ asset('js/catalog-attributes-admin.js') }}" defer></script>
@endsection
