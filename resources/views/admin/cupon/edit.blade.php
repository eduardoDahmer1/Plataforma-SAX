@extends('layout.admin')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Editar Cupom</h1>
    <form action="{{ route('admin.cupons.update', $cupon) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.cupon.partials.form', ['button' => 'Atualizar'])
    </form>
</div>
@endsection
