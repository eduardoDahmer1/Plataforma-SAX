@extends('layout.admin')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Adicionar Cupom</h1>
    <form action="{{ route('admin.cupons.store') }}" method="POST">
        @csrf
        @include('admin.cupon.partials.form', ['button' => 'Salvar'])
    </form>
</div>
@endsection
