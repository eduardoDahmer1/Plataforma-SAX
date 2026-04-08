@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header title="Editar Cupom" description="Atualize as configurações do cupom de desconto">
        <x-slot:actions>
            <a href="{{ route('admin.cupons.index') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
            </a>
        </x-slot:actions>
    </x-admin.page-header>
    <form action="{{ route('admin.cupons.update', $cupon) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.cupon.partials.form', ['button' => 'Atualizar'])
    </form>
</x-admin.card>
@endsection
