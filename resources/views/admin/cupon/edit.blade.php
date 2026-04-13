@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header 
        title="{{ __('messages.editar_cupom_titulo') }}" 
        description="{{ __('messages.atualizar_config_cupom_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.cupons.index') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> {{ __('messages.voltar_listagem_link') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form action="{{ route('admin.cupons.update', $cupon) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.cupon.partials.form', ['button' => __('messages.atualizar_btn')])
    </form>
</x-admin.card>
@endsection