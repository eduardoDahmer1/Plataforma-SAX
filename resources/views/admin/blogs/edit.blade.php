@extends('layout.admin')

@section('content')
<x-admin.card>
    <form id="blogForm" action="{{ route('admin.blogs.update', $blog) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.blogs._form')
    </form>
</x-admin.card>
@endsection
