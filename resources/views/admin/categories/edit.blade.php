@extends('layout.admin')

@section('content')
<x-admin.card>
    @include('admin.categories.partials.form')
</x-admin.card>
@endsection
