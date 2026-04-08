@extends('layout.admin')

@section('content')
<x-admin.card>
    <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.blogs._form')
    </form>
</x-admin.card>
@endsection
